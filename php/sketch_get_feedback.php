<?php
error_log( 'sketch_get_feedback.php start' );

session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

// ===================================================
// User Related Responces
// ===================================================

// Check if we need to update or insert into the table
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

error_log("gotten username: ".$username);

$query = 'SELECT * FROM sketching_user_responces WHERE username=$1';
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
// Is this user RPI Affilated
// ===================================================
$query = 'SELECT rpi_affilate FROM load_user_responces WHERE username=$1';
$result = pg_query_params($query, array($username));
$rpi_affilate = pg_fetch_row($result);
$rpi_affilate = $rpi_affilate[0];


error_log("User Responces: ".$user_responces);

// ===================================================
// Model Related Responces
// ===================================================

// Getting working model from userobj because this contains the model id
$workingModel = $userobj->workingModel;
$id  = $workingModel->id;

error_log("id is: ".$id);

if($id != "Not Assigned"){


    // Using this id we will look into model_meta database and retrive user_model_num
    $query = 'SELECT user_model_num FROM model_meta WHERE id=$1';
    $results = pg_query_params( $query, array($id) );

    if(pg_num_rows($results) != 1) {
        error_log("Duplicate models in model_meta");
        die();
    }

    $row = pg_fetch_row($results);
    $user_model_num = $row[0];

    error_log("model_num is: ".$id);


    $query = 'SELECT * FROM sketching_model_responces WHERE username=$1 AND user_model_num=$2';
    $results = pg_query_params( $query, array($username,$user_model_num) );


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

    // Saving row used at the end
    $model_responces = pg_fetch_row($results);

    error_log("Model Responces: ".$model_responces);

    // ===================================================
    // Setting up for passing to javascript
    // ===================================================

    echo json_encode(array(
        "interesting"=>$user_responces[1], // What did you find fun or interesting in this sketching environment?<br>
        "features"=>$user_responces[2], // What additional features should be added to system to allow greater flexibility in design?
        "limitations"=>$user_responces[3],// Describe some designs that you were not able to create due  to system limitations?
        "dislikes"=>$user_responces[4],// Was there anything you did not like about working in this sketching environment?
        "ui"=>$user_responces[5],// Where there any elements of the user interface that were hard to use or confusing?
        "category"=>$model_responces[2],
        "unlisted_category"=>$model_responces[3],
        "dorm"=>$model_responces[4],
        "unlisted_dorm"=>$model_responces[5],
        "floor"=>$model_responces[6],
        "room"=>$model_responces[7],
        "visited"=>$model_responces[8],
        "frequency"=>$model_responces[9],
        "confidance"=>$model_responces[10],
        "comments"=>$model_responces[11],
        "is_rpi_dorm"=> $model_responces[12],
        "rpi_affilate"=>$rpi_affilate
    ));


}else{


    echo json_encode(array(
        "interesting"=>$user_responces[1], // What did you find fun or interesting in this sketching environment?<br>
        "features"=>$user_responces[2], // What additional features should be added to system to allow greater flexibility in design?
        "limitations"=>$user_responces[3],// Describe some designs that you were not able to create due  to system limitations?
        "dislikes"=>$user_responces[4],// Was there anything you did not like about working in this sketching environment?
        "ui"=>$user_responces[5],// Where there any elements of the user interface that were hard to use or confusing?
        "category"=>"",
        "unlisted_category"=>"",
        "dorm"=>"",
        "unlisted_dorm"=>"",
        "floor"=>"",
        "room"=>"",
        "visited"=>"",
        "frequency"=>"",
        "confidance"=>"",
        "comments"=>"",
        "is_rpi_dorm"=>"",
        "rpi_affilate"=>$rpi_affilate
    ));






}




error_log( 'sketch_get_feedback.php end' );


?>