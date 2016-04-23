<?php

session_start();
require_once("user.php");
require_once('model.php');
require_once("config.inc.php"); // Open connection

error_log( '3d_lsvo_submit.php start' );

// ====================================================================
// User Specific
// ====================================================================

// Does the user want this model to be viewable to public
$publish  = isset($_POST['publish']);

if($publish){
  $str_publish = "true";
}else{
  $str_publish = "false";
}

// Did you understand the results of the simulation, was there anything confusing or unclear
$understand = $_POST['understand'];

// Did the system allow you to create and test daylighting performance with respect to over or under illumination
$limitations = $_POST['limitations'];

// =====================================================================
// Updating user specific questions
// =====================================================================

// Getting username to insert or update user specific feedback
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

$query = 'SELECT * FROM lsvo_user_responces WHERE username=$1';
$results = pg_query_params($query, array($username));
    
// Remove old entry if any exisit
if (pg_num_rows($results) > 0) {

  $query = 'DELETE FROM lsvo_user_responces WHERE username=$1';
  $results = pg_query_params($query, array($username));

}
  
// We need a new entry
$query = 'INSERT into lsvo_user_responces VALUES($1,$2)';
pg_query_params($query, array($username, $limitations));

// =====================================================================
// Render Specific user specific questions
// =====================================================================

// Parsing view path to get model_id,folder_name
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

$query = 'SELECT * FROM lsvo_render_responces WHERE id=$1 AND args=$2';
$results = pg_query_params( $query, array($id,$args) );

// Delete old feedback, update with this one
if(pg_num_rows($results) > 0 ){

  $query = 'DELETE FROM lsvo_render_responces WHERE id=$1 AND args=$2';
  pg_query_params( $query, array($id, $args) );

}

// Insert new entry
$query = 'INSERT into lsvo_render_responces VALUES($1,$2,$3,$4)';
pg_query_params($query, array($id,$args,$understand,$str_publish));

error_log( '3d_lsvo_submit.php end' );
?>
