<html>
    <head>
        <?php
            include('../functions/functions.php');
            // ########################################## Winddir chart ########################################## //
            $wd = getAllWd(); // Get all the time and winddir from the db :  [[time,wd],[time,wd],...]
            //print_r($wd);
            $avgWd = getFreqWd($wd); // Get the frequency of winddir : [{dir:freq},{dir:freq},...]
            //print_r($avgWd);
            $dateList = getDateList($wd); // Get the whole date useful infos
            //print_r($dateList);
            // print_r(getDirs($wd)); to prove nothing was record to the empty directions
        ?>
    </head>
    <body>
        <header style="text-align:center;"><?php include('../template/header.php')?></header>

        <!-- Winddir chart (Winddir)-->
        <canvas id="wd" style="width:10;height:10"></canvas>
        <script type='text/javascript'>
            const wdCtx = document.getElementById('wd').getContext('2d');
            const wdConfig = {
                //type: 'polarArea',
                type: 'radar',
                data: {
                    labels: ["N", "NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSO", "SO", "OSO", "O", "ONO", "NO", "NNO"],
                    datasets: [{
                        label: 'Occurence de direction du vent',
                        data: [<?php echo '"'.implode('","', $avgWd).'"' ?>],
                        /*backgroundColor: [
                                          "rgba(255, 0, 0, 0.2)",
                                          "rgba(119, 9, 112, 0.2)",
                                          "rgba(5, 175, 158, 0.2)",
                                          "rgba(154, 52, 22, 0.2)",
                                          "rgba(204, 165, 19, 0.2)",
                                          "rgba(182, 27, 1, 0.2)",
                                          "rgba(53, 79, 221, 0.2)",
                                          "rgba(108, 214, 47, 0.2)",
                                          "rgba(65, 58, 61, 0.2)",
                                          "rgba(116, 230, 97, 0.2)",
                                          "rgba(160, 33, 173, 0.2)",
                                          "rgba(219, 125, 113, 0.2)",
                                          "rgba(150, 249, 147, 0.2)",
                                          "rgba(204, 219, 96, 0.2)",
                                          "rgba(204, 219, 96, 0.2)",
                                          "rgba(105, 117, 224, 0.2)"
                                          ],*/
                        backgroundColor : "rgba(255, 0, 0, 0.2)",
                        /*borderColor: [
                                          "rgba(255, 0, 0, 1)",
                                          "rgba(119, 9, 112, 1)",
                                          "rgba(5, 175, 158, 1)",
                                          "rgba(154, 52, 22, 1)",
                                          "rgba(204, 165, 19, 1)",
                                          "rgba(182, 27, 1, 1)",
                                          "rgba(53, 79, 221, 1)",
                                          "rgba(108, 214, 47, 1)",
                                          "rgba(65, 58, 61, 1)",
                                          "rgba(116, 230, 97, 1)",
                                          "rgba(160, 33, 173, 1)",
                                          "rgba(219, 125, 113, 1)",
                                          "rgba(150, 249, 147, 1)",
                                          "rgba(204, 219, 96, 1)",
                                          "rgba(204, 219, 96, 1)",
                                          "rgba(105, 117, 224, 1)"
                                          ],*/
                        borderColor : "rgba(255, 0, 0, 1)",
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
                                /*filter: function(legendItem, chartData) {
                                    // returns true if the datasetIndex == 1
                                    if (legendItem.datasetIndex.isDatasetVisible == 1) {
                                      return false;
                                    }
                                   return true;
                                }*/
                            },
                            position:'bottom',
                            align : 'center',
                        },
                    },
                    animations : {
                        //animateRotate: true,
                        //animateScale: true,
                        tension: {
                            duration: 1000,
                            easing: 'linear',
                            from: 1,
                            to: 0,
                        }
                    },
                    scales: {
                        r: {
                          pointLabels: {
                            font: {
                              size: 20
                            }
                          }
                        }
                    }
                }};
                const Wd = new Chart(wdCtx, wdConfig);
                Wd.render();
        </script>
    </body>
</html>