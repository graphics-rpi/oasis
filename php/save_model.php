<?php
  
// ===========================================
// This script saves the model given via post method
// into the database for later retrivial
// ===========================================

session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');


// Required to save to model table for database
// Required for REMESHER
$isNew                  = $_POST['isNew'];
$model_id               = -404;
$username               = "undefined";
$wall_file_contents     = $_POST['wallData'];
$number_of_clicks       = $_POST['numclicks'];
$number_of_walls        = $_POST['numwalls'];
$number_of_windows      = $_POST['numwins'];
$user_model_number      = -404;
$renovation_of_model    = -404;
$time_spent             = $_POST['time'];
$title                  = $_POST['title'];
$comments               = $_POST['comments'];
$concerns               = $_POST['concerns'];
//
//// Required for sketch_ui
$transData              = $_POST['transData'];



/*
//DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//
//DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//
$isNew                  = false;
$model_id               = -404;
$username               = "undefined";
$wall_file_contents     = "debug_wall_file_contents";
$number_of_clicks       = 0;
$number_of_walls        = 0;
$number_of_windows      = 0;
$user_model_number      = -404;
$renovation_of_model    = -404;
$time_spent             = 0;
$title                  = "debug_title";
$comments               = "debug_comments";
$concerns               = "debug_concerns";
$transData              = "viewport 123 242\nmonth 15\nhour 11\n";
//DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//
//DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//
 */
 

echo 'Done Loading in debug values <br>';

// Get $username
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

echo 'Getting username '.$username.'<br>';

// Get model_id unique
$model_id = $userobj->getNewUniqueModelId();

echo 'Getting new unquie id '.$model_id.'<br>';

// Increment model number OR rennovation number
if($isNew == "true"){

  echo 'Working with New Model <br>';

  // Find the users last model made
  $userobj->loadLastModel();

  echo 'loadedLastModel worked<br>';

  $workingModel = $userobj->workingModel;

  // If we have a new user this will set his first model to 1
  $user_model_number = 1 + $workingModel->modelNum;
  $renovation_of_model = 0;

}else{

  echo 'Working with old model<br>';
  // Assuming we already have user model
  $workingModel = $userobj->workingModel;

  $user_model_number = $workingModel->modelNum;
  $renovation_of_model = 1 + $workingModel->renovationNum;

}

//==========================================
// Inserting Models into csdb
//==========================================
$query = "INSERT INTO models VALUES ('$model_id', 
  '$username', '$wall_file_contents', $number_of_clicks, 
  $number_of_walls, $number_of_windows, $user_model_number, 
  $renovation_of_model, $time_spent, '$title', '$comments', '$concerns');";


// //DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//
$file = fopen("../user_output/debug.log", "w");
echo fwrite($file, $query);
 fclose($file);
// //DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG.DEBUG.DDEBUG.DEBUG.DEBUG//

$result = pg_query($query) or die("Failed to upload this wall file");


// Updating pointer to latest model
$update = 
  "UPDATE users 
  SET last_model_id='$model_id' WHERE email='$username';";
$upd_result = pg_query($update) or die("Failed to update into user DB");

//==========================================
// Inserting Trans into transforms
//==========================================
$query = "INSERT INTO transforms VALUES( '$model_id', '$transData' );";
$result = pg_query($query) or die( "Failded to save trans data" );

//==========================================
// Updating user's model to this one
//==========================================
$updated_model = new Model($model_id,$user_model_number,$renovation_of_model,$wall_file_contents."",$transData."");
$userobj->workingModel = $updated_model;
$_SESSION['user'] = serialize($userobj);

//==========================================
// Creating create obj files
//==========================================
$userobj->create_render_files();

/*
// Save as Files both temp_wall_3 temp_tran_3 $wallfilename = "../output/wallfiles/temp_".$model_id.".wall"; $transfilename = "../output/wallfiles/temp_".$model_id.".trans";

$wall_handler = fopen($wallfilename, 'w');
$trans_handler = fopen($transfilename, 'w');

fwrite($wall_handler, $wall_file_contents);
fwrite($trans_handler, $auxdata);

// Run Remesher for new model
$script= "../cgi-bin/run_remesher_and_lsvo.sh";
$wallfile = "/server_data/wallfiles/temp_".$model_id.".wall";

$email_pieces = explode("@",$username);
$dir_name = $email_pieces[0];
$output = "/server_data/users/".$dir_name;

// Execute and log
shell_exec("rm ../output/users/".$dir_name."/slow/*");
shell_exec("rm ../output/users/".$dir_name."/tween/*");

$lsvo_month = $_POST['lsvo_month'];
$lsvo_time = $_POST['lsvo_time'];

// make compass into a session varible
$_SESSION['compass_rot'] = $_POST['compass_rot'];
$_SESSION['month'] = $lsvo_month;
$_SESSION['time'] = $lsvo_time;

// All 3 path's are absolute
$log = shell_exec($script." ".$wallfile." ".$output." ".$lsvo_month." ".$lsvo_time);

$logfilename = "/server_data/wallfiles/temp_".$model_id.".log";
$log_handler = fopen($logfilename, 'w');
fwrite($log_handler, $log);
 */

?>

