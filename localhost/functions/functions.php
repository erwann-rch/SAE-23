<?php
// Db
function dbConnect(){
    $db = new SQLite3('../db/sae23.sqlite') or die("Connexion à la base sqlite impossible");
    return $db;
}

// Time
function getTimestamp($ISO8601){
    $timestamp = strtotime($ISO8601); //ISO8601 to unix timestamp
    return $timestamp; // timestamp = str
}

function getMaxTime(){
    $db = dbConnect();
    $timeRq = $db->query("SELECT MAX(time) as current FROM weather;"); // Select the last time ==> max time bc it's updated chronologically
    $time = $timeRq->fetchArray()['current'];
    return $time; // time = str
}

function getDateList($temp){
    $dateList = array();
    $hM1 = null; // hour[i-1]
    foreach($temp as $line) { // Handles each line of temp as an specific array
        $index = array_search($line,$temp); // Search this array's index
        $h = getdate(getTimestamp($temp[$index][0]))['hours']; // getdate['hours'] of the unix timestamp generated from the ISO8601 of the temp's array
        //echo "h: $h,$index<br>";
        //print_r($temp[$index][0]);
        //echo"<br>";

        if ($h != $hM1) { // Check if the actual hour of the record in the db is different of the previous
            if ($h == 0){ // Only to print the day on the chart ==> 0h = midnight = new day
                $tmp = null;
                if ($tmp != $h){
                    $reversedTemp = array_reverse($temp); // Get the last occurrence of an hour (0 in this case) == get the first occurrence in the reversed array
                    $lastIndex = array_search($h,$reversedTemp); // Get the index of the first occurrence of this hour
                    $d = getdate(getTimestamp($reversedTemp[$lastIndex][0]))['mday']; // getdate['mday'] of ... the temp's reversed array
                }
            }
            $d = getdate(getTimestamp($temp[$index][0]))['mday']; // Get simply the day of the hour
            $hM1 = $h; // set the new $hM1
            //echo "hm1 : $hM1<br>";
            $m = getdate(getTimestamp($temp[$index][0]))['mon']; // Get the month
            $date = "$m,$d,$h"; // Make a str of these values
            //echo $date;
            array_push($dateList,$date); // and put them in the dateList list
        }
        // if not just continue until find a new hour
    }
    return $dateList; // dateList = ["month,day,hour","month,day,hour",...]
}

// Temp
function getAllTemp(){
    $db = dbConnect();
    $tempRq = $db->query("SELECT time,temp FROM weather;"); // Select all temp in the db
    $temp = array();
    while ($tempList = $tempRq->fetchArray()){
        //print_r($tempList);
        array_push($temp,$tempList); // and put them into an array
    };
    return $temp; // temp = [[x,y],[x,y]]
}

function getAvgTemp($temp){ // Calculate the hour average of temp by part of 6 (every 6 entries ==> 1h passed)
    $len = count($temp);
    $tmp = array();
    $avgList = array();
    for($i=1;$i<count($temp)+1;$i++){ // Start to 1 to avoid DivisionByZero error
        if (gettype($len/$i) != gettype(1)){ // Get the type of the quotient and check if it's an integer
            array_push($tmp,$temp[$i][1]); // Put the actual entry into a tmp list
            if (count($tmp) == 6){ // Check if there is 6 entries in this tmp list
                $avgVal = round(array_sum($tmp)/count($tmp),2); // and do the average by summing all the value of the list and dividing them bu the count of them (6)
                //echo"{$avgVal}<br>";
                array_push($avgList,$avgVal); // Put these averages into a list of averages
                $tmp = array();
            }
        }
    }
    //print_r($avgList);
    return $avgList; // avgList = [x,y,z,...]
}

function getMaxTemp(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $tempRq = $db->query("SELECT MAX(temp) as current FROM weather;");
    $temp = $tempRq->fetchArray()['current'];
    return $temp; // temp = float
}

// Feels_Like
function getAllFeels(){ // Same idea of getMaxTme()
    $db = dbConnect();
    $feelRq = $db->query("SELECT time,feels_like FROM weather;");
    $fl = array();
    while ($feelList = $feelRq->fetchArray()){
        //print_r($tempList);
        array_push($fl,$feelList);
    };
    return $fl; // fl = [[x,y],[x,y]]
}

function getAvgFeels($feels){ // Same idea of getAvgTemp()
    $len = count($feels);
    $tmp = array();
    $avgList = array();
    for($i=1;$i<count($feels)+1;$i++){
        if (gettype($len/$i) != gettype(1)){
            array_push($tmp,$feels[$i][1]);
            if (count($tmp) == 6){
                $avgVal = round(array_sum($tmp)/count($tmp),2);
                //echo"{$avgVal}<br>";
                array_push($avgList,$avgVal);
                $tmp = array();
            }
        }
    }
    //print_r($avgList);
    return $avgList; // avgList = [x,y,z,...]
}

function getMaxFeels_Like(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $flRq = $db->query("SELECT MAX(feels_like) as current FROM weather;");
    $fl = $flRq->fetchArray()['current'];
    return $fl; // fl = float
}

// Hum
function getAllHum(){ // Same idea of getAllTime()
    $db = dbConnect();
    $humRq = $db->query("SELECT time,humidity FROM weather;");
    $hum = array();
    while ($humList = $humRq->fetchArray()){
        //print_r($humList);
        array_push($hum,$humList);
    };
    return $hum; // hum = [[x,y],[x,y]]
}

function getAvgHum($hum){ // Same idea of getAvgTemp()
    $len = count($hum);
    $tmp = array();
    $avgList = array();
    for($i=1;$i<count($hum)+1;$i++){
        if (gettype($len/$i) != gettype(1)){
            array_push($tmp,$hum[$i][1]);
            if (count($tmp) == 6){
                $avgVal = round(array_sum($tmp)/count($tmp),2);
                //echo "$avgVal<br>";
                array_push($avgList,$avgVal);
                $tmp = array();
            }
        }
    }
    //print_r($avgList);
    return $avgList; // avgList = [x,y,z,...]
}

function getMaxHum(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $humRq = $db->query("SELECT MAX(humidity) as current FROM weather;");
    $hum = $humRq->fetchArray()['current'];
    return $hum; // hum = float
}

// Wind
function getAllWs(){ // Same idea of getAllTime()
    $db = dbConnect();
    $wsRq = $db->query("SELECT time,speed FROM wind;");
    $ws = array();
    while ($wsList = $wsRq->fetchArray()){
        //print_r($wsList);
        array_push($ws,$wsList);
    };
    return $ws; // ws = [[x,y],[x,y]]
}
function getAvgWs($ws){ // Same idea of getAvgTemp()
    $len = count($ws);
    $tmp = array();
    $avgList = array();
    for($i=1;$i<count($ws)+1;$i++){
        if (gettype($len/$i) != gettype(1)){
            array_push($tmp,$ws[$i][1]);
            if (count($tmp) == 6){
                $avgVal = round(array_sum($tmp)/count($tmp),2);
                //echo "$avgVal<br>";
                array_push($avgList,$avgVal);
                $tmp = array();
            }
        }
    }
    //print_r($avgList);
    return $avgList; // avgList = [x,y,z,...]
}

function getMaxWs(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $wsRq = $db->query("SELECT MAX(speed) as current FROM wind;");
    $ws = $wsRq->fetchArray()['current'];
    return $ws; // ws = float
}

function getAllWd(){ // Same idea of getAllTime()
    $db = dbConnect();
    $wdRq = $db->query("SELECT time,direction FROM wind;");
    $wd = array();
    while ($wdList = $wdRq->fetchArray()){
        //print_r($wdList);
        array_push($wd,$wdList);
    };
    //print_r($wd);
    return $wd; // wd = [[x,y],[x,y]]
}

function getDirs($wd){
    $dirs = array();
    for($i=0;$i<count($wd);$i++){
        if(!in_array($wd[$i][1],$dirs)){
           array_push($dirs,$wd[$i][1]);
        };
    };
    return $dirs;
}
function getFreqWd($wd){ // Get the frequency of a direction in the whole data
    $freqList = array();
    for($i=0;$i<count($wd);$i++){ // Check for each element if the direction exists in the array of array_keys
        if(in_array($wd[$i][1],array_keys($freqList))){
           $freqList[$wd[$i][1]] = $freqList[$wd[$i][1]] + 1; // add one to the value if it's the case
        }
        else{
            $freqList[$wd[$i][1]] = 1; // set to one in the other case
        }
    }
    //print_r($freqList);
    return $freqList; // freqList = [{dir:freq},{dir:freq},...]
}

function getMaxWd(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $wdRq = $db->query("SELECT MAX(direction) as currentDir,MAX(deg) as currentDeg FROM wind;");
    $wd = $wdRq->fetchArray();
    return $wd; // wd = [float,str]
}

// Pressure
function getAllPres(){ // Same idea of getAllTime()
    $db = dbConnect();
    $presRq = $db->query("SELECT time,pressure FROM weather;");
    $pres = array();
    while ($presList = $presRq->fetchArray()){
        //print_r($presList);
        array_push($pres,$presList);
    };
    return $pres; // pres = [[x,y],[x,y]]
}
function getAvgPres($pres){ // Same idea of getAvgTemp()
    $len = count($pres);
    $tmp = array();
    $avgList = array();
    for($i=1;$i<count($pres)+1;$i++){
        if (gettype($len/$i) != gettype(1)){
            array_push($tmp,$pres[$i][1]);
            if (count($tmp) == 6){
                $avgVal = round(array_sum($tmp)/count($tmp),2);
                //echo "$avgVal<br>";
                array_push($avgList,$avgVal);
                $tmp = array();
            }
        }
    }
    //print_r($avgList);
    return $avgList; // avgList = [x,y,z,...]
}

function getMaxPressure(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $presRq = $db->query("SELECT MAX(pressure) as current FROM weather;");
    $pres = $presRq->fetchArray()['current'];
    return $pres;  // pres = float
}

// Sun
function getSunrise(){ // Get the last sunrise record
    $db = dbConnect();
    $sunRq = $db->query("SELECT sunrise as current FROM sun WHERE sunrise IN (SELECT sunrise FROM sun WHERE time IN (SELECT MAX(time) as time FROM sun));");
    $sun = $sunRq->fetchArray()['current'];
    return $sun;  // sun = str
}

function getSunset(){ // Same idea of getSunrise()
    $db = dbConnect();
    $sunRq = $db->query("SELECT sunset as current FROM sun WHERE sunset IN (SELECT sunset FROM sun WHERE time IN (SELECT MAX(time) as time FROM sun));");
    $sun = $sunRq->fetchArray()['current'];
    return $sun;  // sun = str
}

// Last
function getLast(){ // Same idea of getMaxTime()
    $db = dbConnect();
    $lastRq = $db->query("SELECT MAX(last) as current FROM last;");
    $last = $lastRq->fetchArray()['current'];
    return $last;  // last = str
}
// JSON
function getJson($opt){ // Handles CGI arguments
    $data = array();
    if(ISSET($opt['all']) and count($opt) === 1){ // Check if 'all' CGI is selected and if there is no more CGI
         $data = ['Date' => date("Y-m-d h:i:sA"),'Temperature' => getMaxTemp(),'Feels like' => getMaxFeels_Like(),'Wind Speed' => getMaxWs(),'Wind Direction' => getMaxWd()[0],'WInd Degre' => getMaxWd()[1],'Pressure' => getMaxPressure(),'Humidity' => getMaxHum()];
    }
    elseif(ISSET($opt['all']) and count($opt) !== 1){ // if 'all' selected but more CGI
        $data = ['Message' => 'incoherent number of CGI'];
    }
    elseif(count($opt) === 0){ // GGI set (url+?) but options
        $data = ['Message' => 'incoherent number of CGI'];
    }
    else{ // Coherent request
        if(ISSET($opt['temp'])){
            $data['Temperature'] = getMaxTemp();
        }
        if(ISSET($opt['feellike'])){
            $data['Feels like'] = getMaxFeels_Like();
        }
        if(ISSET($opt['windspeed'])){
            $data['Wind Speed'] = getMaxWs();
        }
        if(ISSET($opt['winddir'])){
            $data['Wind Direction'] = getMaxWd()[0];
        }
        if(ISSET($opt['winddeg'])){
            $data['Wind Degre'] = getMaxWd()[1];
        }
        if(ISSET($opt['pressure'])){
            $data['Pressure'] = getMaxPressure();
        }
    }
    return $data; // data = {key:value,key:value,...}
}

function isRegistered($apikey){
    $registered = array();
    if(!array_search($apikey,$registered)){
        return False;
    }else {
        return True;
    }
}

function register($registered,$apikey){
    array_push($registered,$apikey);
}

// Rose des vents moyenne
?>