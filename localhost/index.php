<html>
    <head>
        <?php
            include('functions/functionsIndex.php');
            $lastUpdate = date('Y-m-d h:i:sA', strtotime(getMaxTime()));
            //$last = date('Y-m-d h:i:sA', strtotime(getLast()));
            $currentDate = date("Y-m-d h:i:sA");
            $currentTemp = getMaxTemp();
            $currentFeel_like = getMaxFeels_Like();
            $currentWs = getMaxWs();
            $currentWd = getMaxWd()[0];
            $currentWindDeg = getMaxWd()[1];
            $currentPressure = getMaxPressure();
            $currentHum = getMaxHum();
            $miSize = filesize('db/sae23.sqlite')/1024;
            $mSize = round(filesize('db/sae23.sqlite')/1000,2);
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('template/header.php')?></header>
        <div class='main_container'>
            <div style='text-align:center;'>
                <table style='width:100%;'>
                    <tr>
                        <td style="width:50%">
                            <div class='container_left'>
                                <h4>Current data</h4>
                                <p>Actual <b>time</b> : <b><?=$currentDate?></b></p>
                                <p>Current <b><a href="php/windspeed.php">windspeed</a></b> : <b><?=$currentWs?>m.s^-1</b></p>
                                <p>Current <b><a href="php/winddir.php">wind direction</a></b>: <b><?=$currentWd?></b> or a wind blowing of <b><?=$currentWindDeg?>°</b></p>
                                <p>Currrent <b><a href="php/hum.php">humidity</a></b> : <b><?=$currentHum?>%</b></p>
                                <p>Current <b><a href="php/pressure.php">pressure</a></b> : <b><?=$currentPressure?>hPa</b></p>
                                <p>Current <b><a href="php/temp.php">temperature</a></b> : <b><?=$currentTemp?>°C</b> but feels like <b><?=$currentFeel_like?>°C</b></p>
                            </div>
                        </td>
                        <td style="width:50%">
                            <div class='container_right'>
                                <h4>Database information</h4>
                                <p><b>Last update of the DB</b> : <b><?=$lastUpdate?></b></p>
                                <!---<p><b>Last update of the API</b> : <b><?=$last?></b></p>--->
                                <p><b>Size</b> : <b><?=$miSize?>KiB</b> or <b><?=$mSize?>KB</b></p>
                                <p><a href='db/sae23.sqlite'>Download</a></p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><h4>Sunrise :</h4><br><img src='img/sunrise.png' alt="sunrise" style="width:5%;height:5%;"><?php echo date('Y-m-d h:i:sA', strtotime(getSunrise()))?></td>
                        <td><h4>Sunset :</h4><br><img src='img/sunset.png' alt="sunset" style="width:5%;height:5%;"><?php echo date('Y-m-d h:i:sA', strtotime(getSunset()))?></td>
                    </tr>
                </table>
            </div>
        </div>
        <br><hr>
        <div style='text-align:center;'>
            <form method="post" action='index.php?register=true'>
                <input type="submit" name="register" class="button" value="Register"/>
            </form>
            <?php
                // Generate an api key
                $apikey = implode('-', str_split(substr(strtolower(md5(microtime().rand(1000, 9999))),0, 30), 6));

                if(ISSET($_POST)){
                    if (ISSET($_POST['register'])){
                        session_start();
                        register($apikey); # register the key into the list of allowed keys
                        echo "Your API key : ".$apikey;
                        header("Refresh:2.5,url='json.php?all=true&apikey={$apikey}'");
                    }
                }
            ?>
        </div>
    </body>
</html>