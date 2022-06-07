############################# [ IMPORTS ] #############################
import random, json, requests, time, os

from datetime import datetime
from fake_useragent import UserAgent
from cryptography.fernet import Fernet
from paho.mqtt import client as mqtt_client

############################# [ VARIABLES ] #############################

# Configure the broker
broker = 'test.mosquitto.org'
port = 1883
topic = "sae23/mqtt/erichier"

# Generate client ID with publisher prefix randomly
clientId = f'python-mqtt-{random.randint(0, 1000)}'

# Prepare the query with api URL and headers
# headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko','Cache-Control': 'no-cache, no-store, must-revalidate', 'Pragma': 'no-cache', 'Expires': '0'}
headers = {'User-Agent': f'{UserAgent().random}', 'Cache-Control': 'no-cache, no-store, must-revalidate',
           'Pragma': 'no-cache', 'Expires': '0'}  # Against python caching
url = "https://api.openweathermap.org/data/2.5/weather?q=Suaux&units=metric&lang=fr&appid=e25d2aa6b7750f51895f20213011773b"

usefulData = {}  # Create a dict with all the useful infos to put it in a db


############################# [ FUNCTIONS ] #############################

# Function to get the actual localtime in the good format
def now():
    # print(datetime.now())
    now = str(datetime.now().replace(microsecond=0)).replace(' ', 'T') + "Z"  # ISO 8601 date format
    return now


# --------------------------------------------------
# Function to get the good direction with the degree of the wind
def degToWind(deg):
    index = int((deg / 22.5) + 0.5)  # Get the integer part of the up-rounded deg divided by 22.5 (to get 16 dirs)
    # print(index)
    dirs = ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSO", "SO", "OSO", "O", "ONO", "NO", "NNO"]
    dirr = dirs[(index % len(dirs))]  # Get the right dir by mod(16) the previous index
    # print(dirr)
    return dirr


# --------------------------------------------------
# Get the last update of the api
def getLast(url):
    urlXML = url + "&mode=xml"  # Add the end to get XML format
    xml = requests.get(urlXML, headers=headers).text
    indexSub = xml.find('lastupdate value="')  # Find the substring
    indexStart = indexSub + len(
        ('lastupdate value="'))  # add the length of the substring to get the index of the really wanted substr
    last = xml[indexStart:].split('"')[0] + "Z"  # Get only the lastupdate date by cutting off all the unwanted
    return last


# --------------------------------------------------
# Function to request and get the json file of the api
def getJson(url):
    global usefulData
    # Fetch data from URL
    query = requests.get(url, headers=headers)

    # Get JSON data
    rawData = json.loads(query.text)
    # print(rawData)

    # Process JSON data
    weather = rawData["main"]
    wind = rawData["wind"]
    sun = rawData['sys']

    usefulData['weather'] = {}
    usefulData['wind'] = {}
    usefulData['sun'] = {}

    # Create a dataset
    for key in weather.keys():
        if key not in ['temp_min', 'temp_max', 'sea_level']:
            usefulData['weather'][key] = weather[key]  # Get all the data concerning weather part

    for key in wind.keys():
        if key != 'gust':
            if key == 'deg':
                usefulData['wind'][key] = wind[key]  # Get the value of deg key
                usefulData['wind']['direction'] = degToWind(wind[key])  # Convert deg to direction
            else:
                usefulData['wind'][key] = wind[key]  # Get all the data concerning wind part

    for key in sun.keys():
        if key in ['sunrise', 'sunset']:
            usefulData['sun'][key] = str(
                datetime.fromtimestamp(sun[key]).isoformat()) + "Z"  # from UNIX timestamp to ISO 8601

    # Get XML last update of the api
    usefulData['last'] = {'last': getLast(url)}
    # print(usefulData['last'])

    data = json.dumps(usefulData)  # Put the dict into a str format to be sendable by MQTT protocol
    # print(type(data))
    return data


# --------------------------------------------------
# Function to generate a file to transmit the key used to the good person (use SFTP or other secured app protocol)
def genFileKey(keyfile):
    key = Fernet.generate_key()  # key generation
    with open(keyfile, 'wb') as filekey:
        filekey.write(key)  # put this key in a file


# --------------------------------------------------
# Function to use the keyfile to crypt the message to send
def encryption(keyfile, msg):
    keyFile = os.path.isfile(keyfile)  # Return True if the file exists
    if not keyFile:
        genFileKey(keyfile)  # Generate the filekey if it don't exists

    with open(keyfile, 'rb') as filekey:
        key = filekey.read()

    fernet = Fernet(key)  # Extract the key
    encrypted = fernet.encrypt(msg.encode('utf-8'))  # Encrypt the message using the key

    # print(encrypted)
    return encrypted


# --------------------------------------------------
# Function to create an entity (the client) and connect it to the broker
def connectMQTT():
    def on_connect(client, userdata, flags, rc):  # Callback function
        if rc == 0:
            print("     [+] Connected to MQTT Mosquitto Broker!")
            print("-------------------------------------------------")
        else:
            print("     [-] Failed to connect, return code :", rc)
            exit()

    def on_log(client, userdata, level, buf):
        print("log: ", buf)

    client = mqtt_client.Client(clientId)  # Create a client with the clientId
    client.connect(broker, port, keepalive=650)  # Connect the client to the broker and set a keepalive above the api update time
    client.on_connect = on_connect  # Print the status of the connexion
    client.on_log = on_log
    return client


# --------------------------------------------------
# Function to post a MQTT msg to the topic of the broker
# /!\ DON'T FORGET TO OPTIMIZE (cpu,energy cost,etc)
def publish(client):
    # last = GetLast(url)
    # print(last)
    while True:
        msg = getJson(url)  # Create a msg with the dict of the json request
        encryptedMsg = encryption('filekey.key', msg)  # Encrypt the message to make it travel safe
        result = client.publish(topic, encryptedMsg)  # Post the msg on the topic with the client's id
        status = result[0]  # Get the error code from (result,mid) => result = error code ; mid = nb of msg sent by the client

        if status == 0:  # 0 = No error code => Success
            print(
                f"{result[1]}     [+]     {now()}    '{encryptedMsg}' to topic '{topic}' on the broker '{broker}' succesfully sent")
        else:
            print(f"{result[1]}     [-]     {now()}    Failed to send message to topic {topic}")
        time.sleep(600)  # Update : 10min


# --------------------------------------------------
# Main function
def run():
    try:
        client = connectMQTT()  # Start a connexion
        publish(client)
        client.loop_start()
        time.sleep(0.045)
        time.sleep(0.052)
    except KeyboardInterrupt:
        client.disconnect()
        client.loop_stop()


############################# [ LAUNCH ] #############################

run()
