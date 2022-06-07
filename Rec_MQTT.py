############################# [ IMPORTS ] #############################
import random, time, ast, sqlite3, os

from datetime import datetime
# from cryptography.fernet import Fernet
from paho.mqtt import client as mqtt_client

############################# [ VARIABLES ] #############################

# Configure the broker
broker = 'test.mosquitto.org'
port = 1883
topic = "sae23/mqtt/erichier"

# Generate client ID with pub prefix randomly
clientId = f'python-mqtt-{random.randint(0, 1000)}'

############################# [ FUNCTIONS ] #############################

# Function to get the actual localtime in the good format
def now():
    # print(datetime.now())
    now = str(datetime.now().replace(microsecond=0)).replace(' ', 'T') + "Z"  # ISO 8601 date format
    return now


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
    client.connect(broker, port, keepalive=650)  # Connect the client to the broker
    client.on_connect = on_connect  # Print the status of the connexion
    client.on_log = on_log
    return client


"""
# --------------------------------------------------
# Function to use the keyfile to crypt the message to send
def decryption(keyfile,msg):
    keyFile = os.path.isfile(keyfile)  # Return True if the file exists
    if keyFile :
        with open(keyfile, 'rb') as filekey:
            key = filekey.read()

        fernet = Fernet(key) # Extract the key
        decrypted = fernet.decrypt(msg.decode('utf-8')) # Encrypt the message using the key
    else:
        print("     [-] It's impossible to decrypt the message without the filekey")
        quit()

    #print(decrypted)
    return decrypted
"""
# --------------------------------------------------
# Function to create the tables of the database
db = os.path.isfile("sae23.sqlite")  # Return True if the file exists
def createDB(dict):
    if not db:
        try:
            # Connect to DB and create a cursor
            conn = sqlite3.connect('sae23.sqlite')
            cursor = conn.cursor()

            # Creating table
            keyList1 = []
            keyList2 = []

            for key1 in dict.keys():
                keyList1.append(key1)
                keyList2 = []
                create = f"CREATE TABLE IF NOT EXISTS {key1} (time TEXT NOT NULL PRIMARY KEY,"  # Create a dynamic request
                for key2 in dict[key1].keys():  # List all the key in the current key1
                    keyList2.append(key2)
                    # print(key2)

                for j in range(
                        len(keyList2)):  # Build dynamic string with the values of the current key2 in current key1
                    # print(len(keyList2))
                    if key1 not in ['sun', 'last']:
                        if type(dict[key1][
                                    keyList2[j]]) == str:  # Check if the value of the current key2 in the current key1
                            create += f"{keyList2[j]} TEXT NOT NULL,"
                        else:
                            create += f"{keyList2[j]} REAL NOT NULL,"
                    else:
                        create += f"{keyList2[j]} TEXT NOT NULL UNIQUE on conflict ignore,"  # Get a unique sunrise, sunset and lastupdate for the whole day to lighter the db
                create = create[:-1] + ");"  # Cut the last comma and put the last bracket to end the query
                # print(create)
                """
                # uncomment idf willing to see the sql
                with open('bdd.sql', 'a') as bdd:
                    bdd.write(create + "\n")
                """
                cursor.execute(create)
                # print(keyList2)
            # print(f"}{keyList1}{)

            conn.commit()
            cursor.close()
            status = 0

        except sqlite3.Error as error:  # Handle errors
            print("     [-] Error occured - ", error)
            status = 1
    else:
        status = 1
    return status


# --------------------------------------------------
# Function to create the dataset
def insertInto(dict):
    try:
        # Connect to DB and create a cursor
        conn = sqlite3.connect('sae23.sqlite')
        cursor = conn.cursor()

        # Creating dataset
        keyList1 = []
        keyList2 = []

        for key1 in dict.keys():
            keyList1.append(key1)
            keyList2 = []
            insert = f"INSERT INTO {key1} (time,"  # Create a dynamic request
            for key2 in dict[key1].keys():  # List all the key in the current key1
                keyList2.append(key2)
                # print(key2)

            for j in range(len(keyList2)):  # Build dynamic string with the values of the current key2 in current key1
                insert += f"{keyList2[j]},"
            insert = insert[
                     :-1] + f") VALUES ('{now()}',"  # Cut the last comma and put the last bracket to end the query
            for k in range(len(keyList2)):
                if type(dict[key1][keyList2[k]]) == str:  # Check if the value of the current key2 in the current key1
                    insert += f"'{dict[key1][keyList2[k]]}',"
                else:
                    insert += f"{dict[key1][keyList2[k]]},"
            insert = insert[:-1] + ");"
            # print(insert)
            """
            # uncomment if willing to see the sql
            with open('bdd.sql', 'a') as bdd:
                bdd.write(create + "\n")
            """
            cursor.execute(insert)

            # print(keyList2)
        # print(f"}{keyList1}{)
        conn.commit()
        cursor.close()
        status = 0

    except sqlite3.Error as error:  # Handle errors
        print(" [-] Error occured - ", error)
        status = 1

    return status


# --------------------------------------------------
count = 0
def subscribe(client:mqtt_client):
    def on_message(client, userdata, msg):
        global count
        count += 1
        """
        print(msg)
        print(msg.payload)
        decrypted = decryption('filekey.key',msg.payload)
        print(count)
        """
        print(f"{count}    [+]      {now()}     Received '{msg.payload.decode('utf-8')}' from '{msg.topic}' topic")
        dict = ast.literal_eval(msg.payload.decode('utf-8'))  # str to dict
        if count <= 1:  # Check if it's the first message received
            if createDB(dict) == 0:  # check if db exists and data to it
                print(f"     [+] Database successfully created")
                if insertInto(dict) == 0:  # Check if there is a pb in the inserting
                    print(f"     [+] Dataset successfully inserted\n")
                else:
                    print(f"     [-] Error in the creation of the dataset\n")
            else:  # Db already exists
                if db:  # Check if there is a db file
                    print(f"     [+] Database already exists")
                    if insertInto(dict) == 0:  # Still try to insert data
                        print(f"     [+] Dataset successfully inserted\n")
                    else:
                        print(f"     [-] Error in the creation of the dataset\n")

                else:
                    print(f"     [-] Error in the creation of the database")
                    exit()
        else:
            if insertInto(dict) == 0:  # Just do the insert into because db is already created
                print(f"     [+] Dataset successfully inserted\n")
            else:
                print(f"     [-] Error in the creation of the dataset\n")

    client.subscribe(topic)
    client.on_message = on_message


# --------------------------------------------------
# Main function
def run():
    try:
        client = connectMQTT()  # Start a connexion
        subscribe(client)
        client.loop_forever()
        time.sleep(0.045)
        time.sleep(0.052)
    except KeyboardInterrupt:
        client.disconnect()
        client.loop_stop()

############################# [ LAUNCH ] #############################

run()
