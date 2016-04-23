<?php
error_log( '3d_remesh_get_feedback.php start' );

session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

// ===========================
// User Specific
// ===========================

$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

error_log("gotten username: ".$username);

$query = 'SELECT * FROM remesh_user_responces WHERE username=$1';
$results = pg_query_params($query, array($username));
    
if (pg_num_rows($results) == 0) 
{
    error_log("No previous entries found");
    die();
}

if (pg_num_rows($results) > 1) 
{
    error_log( 'More than 1 entry saved in database' );
    die();
}

// Saving the row, used at the end
$user_responces = pg_fetch_row($results);


// ===================================================
// Model Related Responces
// ===================================================

// Getting working model from userobj because this contains the model id
$workingModel = $userobj->workingModel;
$id  = $workingModel->id;

if( $id == "No Assigned"){
	error_log("Id is not assigned");
	die();
}

$query = 'SELECT * FROM remesh_model_responces WHERE id=$1';
$results = pg_query_params( $query, array($id) );


if (pg_num_rows($results) == 0) 
{
    error_log("No previous entries found");
    echo json_encode(array(
        "impression"=>$user_responces[1],
        "failures"=>$user_responces[2]
    ));
    die();
}

if (pg_num_rows($results) > 1) 
{
    error_log( 'More than 1 entry saved in database' );
    die();
}

// Saving row used at the end
$model_responces = pg_fetch_row($results);

error_log("Model Responces: ".$model_responces);
error_log("User Responces: ".$user_responces);

echo json_encode(array(
	"impression"=>$user_responces[1],
	"failures"=>$user_responces[2],
	"correct"=>$model_responces[1]
));

error_log( '3d_remesh_get_feedback.php end' );

?>