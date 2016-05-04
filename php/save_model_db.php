<?php
session_start();
require_once('config.inc.php'); // Connect to DB
require_once('user.php');
require_once('model.php');

// Get user object
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;

// We need to first update our session variable
$id = "Not Assigned";

// Meta
$title          = $userobj->workingModel->title;
$user_model_num = "Not Assigned";
$user_renov_num = 0;

// Data
$wallfile_txt = $userobj->workingModel->wallfile_txt;
$paths_txt    = $userobj->workingModel->paths_txt;

// Step 1: Get a random alphanumberic string of size 6

error_log("Getting random string");

$valid_id = False;
$id = "";
while($valid_id == False)
{

  // Generate alphanum ID
  $str = "";
  $length = 6;
  $characters = array_merge(range('A','Z'), range('a','z'), range('0','9'));
  $max = count($characters) - 1;
  for ($i = 0; $i < $length; $i++) {
    $rand = mt_rand(0, $max);
    $str .= $characters[$rand];
  }

  // Check the database
  $sql = "SELECT id FROM model_meta WHERE id=$1";
  $res = pg_query_params($sql,array($str));

  // Step 2: Check if exising model, if not continue
  if(pg_num_rows($res) == 0){

    // Step 3: This is my new ID
    $id = $str;
    $valid_id = True;

  }

}

error_log("Getting random string: ".$id);

// Getting unique Model id
// $cmd = "SELECT id FROM model_meta;";
// $res = pg_query($cmd);
// $id  = pg_num_rows($res);


// Getting model numbers ordered
$cmd = "SELECT id FROM model_meta WHERE username='$username' AND user_renov_num=0;";
$res = pg_query($cmd);
$user_model_num = pg_num_rows($res);

// Update the working/session model
$session_model = new Model(
  $id, 
  $title, 
  $user_model_num, 
  $user_renov_num, 
  $wallfile_txt, 
  $paths_txt
);

// Because the model is saved, this is no longer
// just a new model.
$session_model->setStatus("Exisiting");

$userobj->workingModel = $session_model;

$_SESSION['user']      = serialize($userobj);

// Now that our session is sync with ui
// We send this to the database
$userobj->save_to_database();

  pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
    date("Y-m-d").'|'.date("h:i:sa"),
    $username,
    $id,
    "save_model_db.php",
    ""
  ));

?>
