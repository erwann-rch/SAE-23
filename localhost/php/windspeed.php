<html>
    <head>
        <?php
            include('../functions/functions.php');
            // ########################################## Windspeed chart ########################################## //
            $ws = getAllWs(); // Get all the time and windspeed from the db :  [[time,ws],[time,ws],...]
            //print_r($ws);
            $avgWs = getAvgWs($ws); // Get the average of windspeed by hour : [avg,avg,...]
            //print_r($avgWs);
            $dateList = getDateList($ws); // Get the whole date useful infos
            //print_r($dateList);
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('../template/header.php')?></header>

        <!-- Windspeed chart (windspeed)-->
        <canvas id="ws" width="100%" height="30%"></canvas>
        <script type='text/javascript'>
            const wsCtx = document.getElementById('ws').getContext('2d');
            const wsConfig = {
                type: 'line',
                data: {
                    labels: [<?php echo '"'.implode('","', $dateList).'"' ?>],
                    datasets: [{
                        label: 'Vitesse du vent',
                        data: [<?php echo '"'.implode('","', $avgWs).'"' ?>],
                        backgroundColor: 'rgba(247, 162, 0, 0.2)',
                        borderColor: 'rgba(247, 162, 0, 1)',
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
                                text : 'Vitesse du vent (m.s^-1)',
                                font: {
                                    size: 20,
                                },
                            },
                            ticks: {
                                // Include unit in the ticks
                                callback: function(value, index, values) {
                                    //console.log("y:", value,index)
                                    return value +"m.s^-1";
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
                const Ws = new Chart(wsCtx, wsConfig);
                Ws.render();
        </script>
    </body>
</html>