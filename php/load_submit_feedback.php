<?php
session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

error_log( 'load_submit.php start' );
// Collect and save user responces in load_tab.php (We are using POST)

// We they included in our user study
$include = isset($_POST['include']);

// Are you affiliated with Rensselaer Polytechnic Institute?
$rpi_affilate = $_POST['rpi_affilate'];

if($rpi_affilate == "true"){
    // How are you affiliated with RPI?
    $affiliation  = $_POST['affiliation'];
}else{
    $affiliation  = "";
}

// Years of formal education in architecture?
$arch_edu = $_POST['arch_edu'];

// Years of formal education in visual arts?
$visual_edu = $_POST['visual_edu'];

// Years of job experience in architecture? (including internships)
$arch_exp = $_POST['arch_exp'];

// Years of job experience in visual arts? (including internships)
$visual_exp = $_POST['visual_exp'];


if( isset($_POST['software_list'])){
  
  // Have you used any of the following modeling software?
  $software_list = $_POST['software_list'];

  $software_list_str = "";

  // Have to convert into a string to save into db
  foreach($software_list as $tmp)
  {
      $software_list_str .= $tmp;
      
      // Make sure we don't place sperating comma at end of list
      if($software_list[count($software_list)-1] != $tmp)
      {
          $software_list_str .= ',';
          
      }
  }

}else{

  $software_list = "";
}


// Have you used any other unlisted modeling software?
$other_software = $_POST['other_software'];

// Years of experience with modeling softwares?
$software_exp = $_POST['software_exp'];

// Other relevant education or experiences? 
$other_exp = $_POST['other_exp'];

// Are you colorblind? (optional)
$color_blind = $_POST['color_blind'];

// Is it okay if we follow up questions about model's created in our system?
$user_email = $_POST['user_email'];

// Check if we need to update or insert into the table
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

$query = 'SELECT * FROM load_user_responces WHERE username=$1';
$results = pg_query_params($query, array($username));
    
if (pg_num_rows($results) > 0) 
{

  // Remove old entry
  $query = 'DELETE FROM load_user_responces WHERE username=$1';
  $results = pg_query_params($query, array($username));

}
  
// We need a new entry
$query = 'INSERT into load_user_responces VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13)';
pg_query_params($query, array($username,$rpi_affilate,$affiliation,$arch_edu,$visual_edu,$arch_exp,$visual_exp,$software_list_str,$other_software,$software_exp,$other_exp,$color_blind,$user_email));



// We are going to update our check box if needed

// ============================================
// Getting if they are registered in study
// ============================================
$query = 'SELECT include FROM users WHERE email=$1';
$results =  pg_query_params($query, array($username));

if (pg_num_rows($results) == 0) 
{
    error_log("user is not registered");
    die();
}

if (pg_num_rows($results) > 1) 
{
    error_log( 'More than 1 user with this name saved in database');
}

error_log("results for include".$results);

$row =  pg_fetch_row($results);
$res = $row[0];

error_log("include".$include);

if($include){
  $str_inc = "true";
}else{
  $str_inc = "false";
}

// Do we need to update our table?
if($str_inc != $res ){

  $query = 'UPDATE users SET include=$1 WHERE email =$2';
  pg_query_params($query, array($str_inc, $username));

}


error_log("load_submit_feedback ended");

?>
