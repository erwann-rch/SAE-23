<html>
    <head>
        <?php
            // ########################################## Temp chart ########################################## //
            include('../functions/functions.php');
            $temp = getAllTemp(); // Get all the time and temp from the db :  [[time,temp],[time,temp],...]
            //print_r($temp);
            $avgTemp = getAvgTemp($temp); // Get the average of temps by hour : [avg,avg,...]
            //print_r($avgTemp);
            $feelsLike = getAllFeels(); // Get all the time and feels_like from the db :  [[time,temp],[time,temp],...]
            //print_r($feelsLike);
            $avgFeels = getAvgFeels($feelsLike); // Get the average of feels_like by hour : [avg,avg,...]
            //print_r($avgFeels);
            $dateList = getDateList($temp); // Get the whole date useful infos
            //print_r($dateList);
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('../template/header.php')?></header>
        <!--Temperature chart (temp/feels_like)-->
        <canvas id="temp" width="100%" height="30%"></canvas>
        <script type='text/javascript'>
            const tempCtx = document.getElementById('temp').getContext('2d');
            const tempConfig = {
                type: 'line',
                data: {
                    labels: [<?php echo '"'.implode('","', $dateList).'"' ?>],
                    datasets: [{
                        label: 'Température réelle',
                        data: [<?php echo '"'.implode('","', $avgTemp).'"' ?>],
                        backgroundColor: 'rgba(82, 78, 183,0.2)',
                        borderColor: 'rgba(82, 78, 183,1)',
                        borderWidth: 3,
                        fill: true,
                    },
                    {
                        label: 'Température resssentie',
                        data: [<?php echo '"'.implode('","', $avgFeels).'"' ?>],
                        backgroundColor: 'rgba(243,41,61,0.2)',
                        borderColor: 'rgba(243,41,61,1)',
                        borderWidth: 3,
                        fill: true,
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
                                text : 'Température (°C)',
                                font: {
                                    size: 20,
                                },
                            },
                            ticks: {
                                // Include unit in the ticks
                                callback: function(value, index, values) {
                                    //console.log("y:", value,index)
                                    return value +"°C";
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
                }
            };

            const Temp = new Chart(tempCtx, tempConfig);
            Temp.render();
        </script>
    </body>
</html>