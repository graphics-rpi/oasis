<?php

  session_start();
  require_once('config.inc.php');          // Connect to DB
  require_once('user.php');
  require_once('model.php');

  // This is a script that will take all the wall models in the database and 
  // dump them into a folder for evaluation with the remesher

  
  // Making a query in order to collec the models from a list
  $query = "SELECT wall_file_contents FROM models;";
  $result = pg_query($query) or die('Failed to gather all wall models');

  $count = 0;
  while($wall_contents = pg_fetch_row($result) ){

    // Create a file and write to it
    echo "ID:".$wall_contents;
    $file = fopen("../user_output/all_models/model_$count.wall","w");
    fwrite($file, $wall_contents);
    fclose($file);
    $count = $count + 1;

  }
?>

<h1> DUMPING FILES RIGHT NOW </h1>
