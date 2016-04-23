<?php

error_log("task_daylight.php: Start");

session_start();
require_once('config.inc.php');          
require_once('user.php');
require_once('model.php');

// Get user object and username and session model
$userobj       = unserialize($_SESSION['user']);
$data    = $userobj->get_user_simulations();

for( $i=0; $i < count($data[0]); $i++)
{ 


  if($i > 100){ break; }

  $path      =       $data[0][$i];
  $id        =       $data[1][$i];
  $month     =       $data[2][$i];
  $day       =       $data[3][$i];
  $hour      =       $data[4][$i];
  $minute    =       $data[5][$i];
  $weather   =       $data[6][$i];


  error_log('Entry: '.$path.' '.$id.' '.$month.' '.$day.' '.$hour.' '.$minute.' '.$weather); 
}

?>
