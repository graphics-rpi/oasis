<?php
session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

error_log( '3d_remesh_submit.php start' );

// ====================================================================
// Model Specific
// ====================================================================

// Does the 3D generated model match your intentions?
if( isset($_POST['correct']) ){
	$correct = $_POST['correct'];
}else{
	$correct = "";
}

// ====================================================================
// User Specific
// ====================================================================

// Describe your overall impression of the software for determining the interior vs exterior space in your designs?
$impression = $_POST['impression'];

// For the case when the system's interpretation of the interior/exterior of your design was incorrect where was the system wrong?
$failures = $_POST['failures'];

// =====================================================================
// Updating user specific questions
// =====================================================================

// Getting username to insert or update user specific feedback
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

$query = 'SELECT * FROM remesh_user_responces WHERE username=$1';
$results = pg_query_params( $query, array($username));
    

// Remove old entry if any exisit
if (pg_num_rows($results) > 0) {

  $query = 'DELETE FROM remesh_user_responces WHERE username=$1';
  $results = pg_query_params($query, array($username));

}
  
// We need a new entry
$query = 'INSERT into remesh_user_responces VALUES($1,$2,$3)';
pg_query_params($query, array($username,$impression,$failures));

// =====================================================================
// Model Specific user specific questions
// =====================================================================

// Getting working model from userobj because this contains the model id
$workingModel = $userobj->workingModel;
$id  = $workingModel->id;

$query = 'SELECT * FROM remesh_model_responces WHERE id=$1';
$results = pg_query_params( $query, array($id) );

// Delete old feedback, update with this one
if(pg_num_rows($results) > 0 ){

  $query = 'DELETE FROM remesh_model_responces WHERE id=$1';
  pg_query_params( $query, array($id) );

}

// Insert new entry
$query = 'INSERT into remesh_model_responces VALUES($1,$2)';
pg_query_params($query, array($id,$correct));

error_log( '3d_remesh_submit.php end' );
?>

