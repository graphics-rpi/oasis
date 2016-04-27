<?php

session_start();
require_once('config.inc.php');          // Connect to DB

function currentModelInfo($path){
    $pieces = explode("/",$path);
    $pieces = explode("_",$pieces[5]);

    //0 is sim, 1 is date, 2 is time, 3 is weather
    $date = $pieces[1];
    $time = $pieces[2];
    $timezone = $pieces[3];
    $weather = $pieces[4];

    $mon    = substr($date,0,2);
    $dy     = substr($date,2,2);
    $hr    = substr($time,0,2);
    $mi    = substr($time,2,2);
    
    if(strlen($timezone) == 6){
      $tzs     = substr($timezone,0,1);
      $tzhr    = substr($timezone,2,2);
      $tzmi    = substr($timezone,4,2);
    }
    else if(strlen($timezone) == 5){
      $tzs     = "+";
      $tzhr    = substr($timezone,1,2);
      $tzmi    = substr($timezone,3,2);
    }

    $output = "<b>Simulation Info</b><br>";
    $output = $output."Date: ".$mon."/".$dy."<br>";
    $output = $output."Time: ".$hr.":".$mi."<br>";
    $output = $output."Timezone: GMT ".$tzs.$tzhr.":".$tzmi."<br>";
    $output = $output."Weather: ".$weather."<br>";
    return $output;
}
$spath = $_POST["spath"];

echo json_encode(array("result"=>currentModelInfo($spath)));

?>