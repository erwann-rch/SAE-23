<html>
    <head>
        <?php
            include('../functions/functions.php');
            // ########################################## Hum chart ########################################## //
            $hum = getAllHum(); // Get all the time and humidity from the db :  [[time,temp],[time,temp],...]
            //print_r($hum);
            $avgHum = getAvgHum($hum); // Get the average of humidity by hour : [avg,avg,...]
            //print_r($avgHum);
            $dateList = getDateList($hum); // Get the whole date useful infos
            //print_r($dateList);
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('../template/header.php')?></header>

        <!-- Humidity chart (humidity)-->
        <canvas id="hum" width="100%" height="30%"></canvas>
        <script type='text/javascript'>
            const humCtx = document.getElementById('hum').getContext('2d');
            const humConfig = {
                type: 'line',
                data: {
                    labels: [<?php echo '"'.implode('","', $dateList).'"' ?>],
                    datasets: [{
                        label: 'Humidité',
                        data: [<?php echo '"'.implode('","', $avgHum).'"' ?>],
                        backgroundColor: 'rgba(12, 182, 12, 0.2)',
                        borderColor: 'rgba(12, 182, 12, 1)',
                        borderWidth: 3,
                        //fill: true,
                    }]
                },
                options: {
                    layout: {
                         padding: {
                            left: 50,
                            right: 30,
                            top: 3,
                            bottom: 2,
                         },
                    },
                    plugins: {
                        legend: {
                            labels: {
                                // This more specific font property overrides the global property
                                font: {
                                    size: 20,
                                },
                            },
                            position:'bottom',
                            align : 'center',
                        },
                    },
                    animations : {
                        tension: {
                            duration: 1000,
                            easing: 'linear',
                            from: 1,
                            to: 0.4,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            title: {
                                display:true,
                                text : 'Humidité (%)',
                                font: {
                                    size: 20,
                                },
                            },
                            ticks: {
                                // Include unit in the ticks
                                callback: function(value, index, values) {
                                    //console.log("y:", value,index)
                                    return value +"%";
                                },
                            },
                        },
                        x : {
                            ticks: {
                                callback: function(label) {
                                    let realLabel = this.getLabelForValue(label)
                                    var hour = realLabel.split(",")[2];
                                    //console.log(hour)
                                    return hour +"h";
                                }
                            }
                        },
                        xAxis2: {
                            type: "category",
                            title: {
                                display:true,
                                text : 'Heure (h)',
                                font: {
                                    size: 20,
                                },
                            },
                            ticks: {
                                  callback: function(label) {
                                        let realLabel = this.getLabelForValue(label)
                                        var month = realLabel.split(",")[0];
                                        var day = realLabel.split(",")[1];
                                        var hour = realLabel.split(",")[2];
                                        //console.log(day);
                                        if (hour == 0) {
                                            if (month < 10) {
                                                return `${day}/0${month}`
                                            }
                                            return `${day}/${month}`;
                                        }
                                  }
                            }
                        },
                    }
                }};
                const Hum = new Chart(humCtx, humConfig);
                Hum.render();
        </script>
    </body>
</html>