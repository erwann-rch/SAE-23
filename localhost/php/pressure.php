<html>
    <head>
        <?php
            // ########################################## Presure chart ########################################## //
            include('../functions/functions.php');
            $pres = getAllPres(); // Get all the time and presure from the db :  [[time,pres],[time,pres],...]
            //print_r($pres);
            $avgPres = getAvgPres($pres); // Get the average of presure by hour : [avg,avg,...]
            //print_r($avgPres);
            $dateList = getDateList($pres); // Get the whole date useful infos
            //print_r($dateList);
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('../template/header.php')?></header>
        <!--Pressure chart (pressure)-->
        <canvas id="pres" width="100%" height="30%"></canvas>
        <script type='text/javascript'>
            const PresCtx = document.getElementById('pres').getContext('2d');
            const PresConfig = {
              type: 'bar',
              data: {
              labels: [<?php echo '"'.implode('","', $dateList).'"' ?>],
              datasets: [{
                type: 'bar',
                data: [<?php echo '"'.implode('","', $avgPres).'"' ?>],
                backgroundColor: 'rgba(27, 185, 94, 0.2)',
                borderColor: 'rgba(27, 185, 94, 1)',
                borderWidth: 3,
                fill: true,
              },
              {
                type: 'line',
                label: 'Pression atmosphérique',
                data: [<?php echo '"'.implode('","', $avgPres).'"' ?>],
                backgroundColor: 'rgba(82, 78, 183,0.2)',
                borderColor: 'rgba(82, 78, 183,1)',
                borderWidth: 3,
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
                                filter: function(legendItem, chartData) {
                                    // returns true if the datasetIndex == 1
                                    if (legendItem.datasetIndex != 1) {
                                      return false;
                                    }
                                   return true;
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
                                text : 'Pression atmosphérique (hPa)',
                                font: {
                                    size: 20,
                                },
                            },
                            ticks: {
                                // Include unit in the ticks
                                callback: function(value, index, values) {
                                    //console.log("y:", value,index)
                                    return value +"hPa";
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
                }};
            const Pres = new Chart(PresCtx, PresConfig);
            Pres.render();
        </script>
    </body>
</html>