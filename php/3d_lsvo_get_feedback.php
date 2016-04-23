<?php

error_log( '3d_lsvo_get_feedback.php start' );

session_start();

require_once("user.php");
require_once('model.php');
require_once("config.inc.php"); // Open connection

// =============================================
// Feedback we are trying to get
// =============================================
$understand = "";   // (model specific)
$limitations = "";  // (user specific)
$publish = "";


// =============================================
// User Specific
// =============================================
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;

error_log("gotten username: ".$username);

$query = 'SELECT * FROM lsvo_user_responces WHERE username=$1';
$results = pg_query_params($query, array($username));
    

// did we get a result?
if (pg_num_rows($results) == 0 or pg_num_rows($results) > 1) 
{
    error_log("No previous entries found or more than 1 entry found"); // die();

}else{

  $user_responces = pg_fetch_row($results);
  error_log("User Responces: ".$user_responces);

  // setting questions
  $limitations = $user_responces[1];

}



// ===================================================
// Model Related Responces
// ===================================================

// Parsing view path to get model_id
$view_path = $_SESSION['view_path'];
$view_path_array = explode("/",$view_path);

$raw_id   = explode("_",$view_path_array[3]); //model_<id>
$raw_args = explode("_",$view_path_array[5]); //sim_<args>_weather

$id = $raw_id[1]; //<id>
$date    = $raw_args[1];
$time    = $raw_args[2];
$zone    = $raw_args[3];
$weather = $raw_args[4];

$args = $date.'_'.$time.'_'.$zone.'_'.$weather;


// Checking to see if we have an assigned id
if( $id == "Not Assigned"){

	error_log("Id is not assigned"); // die();

}else{

  $query = 'SELECT * FROM lsvo_render_responces WHERE id=$1 AND args=$2';
  $results = pg_query_params( $query, array($id,$args) );

  if (pg_num_rows($results) == 0) {

      error_log("No previous entries found");

  } else if (pg_num_rows($results) > 1) {

      error_log( 'More than 1 entry saved in database' );
  } else {

    // Saving row used at the end
    $model_responces = pg_fetch_row($results);
    $understand = $model_responces[2];
    $publish    = $model_responces[3];

  }
}

echo json_encode(array( "understand"=>$understand, "limitations"=>$limitations, "publish"=>$publish));
error_log( '3d_remesh_get_feedback.php end' );

?>
