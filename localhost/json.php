<?php
include('functions/functionsIndex.php');

if(ISSET($_GET)){
    if(ISSET($_GET['apikey'])){;
        $apikey = $_GET['apikey'];
        //echo $apikey;
        session_start();
        if (isRegistered($apikey)){
            header('Content-type: application/json');
            echo json_encode(getJson($_GET));
        }
        else {
            $data = ['Message' => 'unregistered'];
            //print_r($data);
            header('Content-type: application/json');
            echo json_encode($data);
            //header("Refresh:1.5,url='index.php'");
        }
    }
    else{
        $data = ['Message' => 'please mention your api key'];
        //print_r($data);
        header('Content-type: application/json');
        echo json_encode($data);
    }
}
else {
    $data = ['Message' => 'incoherent number of CGI'];
    //print_r($data);
    header('Content-type: application/json');
    echo json_encode($data);
}
?>