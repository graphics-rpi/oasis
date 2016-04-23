<?php
session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

error_log( 'sketch_submit.php start' );

// Collect and save user responces in sketch_tab.php (We are using POST)

// =====================================================================
// Model specific
// =====================================================================

// What category does this model fall into? 
$category = $_POST['category'];

if($category == "other"){
  $unlisted_category = $_POST['unlisted_category'];
}else{
  $unlisted_category = ""; 
}

// What dorm is this a model of?
$dorm = $_POST['dorm'];
$is_rpi_dorm = ""; // blank until answered

if( $dorm == "other" ){

  $is_rpi_dorm = $_POST['is_rpi_dorm']; // true or false string

  // We need to check
  $unlisted_dorm = $_POST['unlisted_dorm'];

}else{

  $unlisted_dorm = "";

}

// What floor were you on? 
$floor = $_POST['floor'];

// What was your room number?
$room = $_POST['room'];

// When was the last time you visited this space? 
$visited = $_POST['visited'];

// How often did you visit this space?
$frequency = $_POST['frequency'];

// How confident are you in modeling this space?
if( isset($_POST['confidance']) ){

  $confidance = $_POST['confidance'];

}else{

  $confidance = "";

}

// Any addition information about model you would like to share?
$comments = $_POST['comments'];

// =====================================================================
// Past this point these are all user specific 
// =====================================================================

// What did you find fun or interesting in this sketching environment?<br>
$interesting = $_POST['interesting'];

// What additional features should be added to system to allow greater flexibility in design?
$features = $_POST['features'];

// Describe some designs that you were not able to create due  to system limitations?
$limitations = $_POST['limitations'];

// Was there anything you did not like about working in this sketching environment?
$dislikes = $_POST['dislikes'];

// Where there any elements of the user interface that were hard to use or confusing?
$ui = $_POST['ui'];


// =====================================================================
// Updating user specific questions
// =====================================================================

// Getting username to insert or update user specific feedback
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

error_log('looking for exisiting entry in sketching_user_responces');


$query = 'SELECT * FROM sketching_user_responces WHERE username=$1';
$results = pg_query_params( $query, array($username) ) or die("Failed 1");
    

// Remove old entry if any exisit
if (pg_num_rows($results) > 0) {

  error_log('exisiting entry in sketching_user_responces');

  $query = 'DELETE FROM sketching_user_responces WHERE username=$1';
  $results = pg_query_params($query, array($username)) or die("Failed 2");

  error_log('removed entry in sketching_user_responces');


}
  
error_log('preping to add entry in sketching_user_responces');


// We need a new entry
$query = 'INSERT into sketching_user_responces VALUES($1,$2,$3,$4,$5,$6)';
pg_query_params($query, array($username, $interesting,$features,$limitations,$dislikes,$ui)) or die("Failed 3");


error_log('added entry in sketching_user_responces');

// =====================================================================
// Model Specific user specific questions
// =====================================================================

// Getting working model from userobj because this contains the model id
$workingModel = $userobj->workingModel;
$id  = $workingModel->id;

error_log('session: model_id '.$id );                  


error_log("Going to query to find model_num");
// Using this id we will look into model_meta database and retrive user_model_num
$query = 'SELECT user_model_num FROM model_meta WHERE id=$1';
$results = pg_query_params( $query, array($id)) or die("Failed 4");



if(pg_num_rows($results) != 1) {
    error_log("Duplicate models in model_meta");
    die();
}


$row = pg_fetch_row($results);
$user_model_num = $row[0];

error_log('session: model_num '.$user_model_num ); 

error_log("Going to check sketching_model_responces if model specific question exisit");

$query = 'SELECT * FROM sketching_model_responces WHERE username=$1 AND user_model_num=$2';
$results = pg_query_params( $query, array($username,$user_model_num) ) or die("Failed 5") ;


// Delete old feedback, update with this one
if(pg_num_rows($results) > 0 ){

  error_log("sketching_model_responces  model specific question exisit");

  $query = 'DELETE FROM sketching_model_responces WHERE username=$1 AND user_model_num=$2';
  pg_query_params( $query, array($username,$user_model_num) ) or die("Failed 6") ;

  error_log("sketching_model_responces  model specific question deleted");


}

error_log("sketching_model_responces  model specific question to be added");  

// Insert new entry
$query = 'INSERT into sketching_model_responces VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13)';
pg_query_params($query, array($username,$user_model_num,$category,$unlisted_category,$dorm,$unlisted_dorm,$floor,$room,$visited,$frequency,$confidance,$comments,$is_rpi_dorm)) or die("Failed 7");
 
 error_log("sketching_model_responces  model specific question added");  

error_log( 'sketch_submit.php end' );

?>

