<?php
error_log( 'load_get_feedback.php start' );

session_start();
require_once("user.php");
require_once('model.php');

// Open connection
require_once("config.inc.php");

// Check if we need to update or insert into the table
$userobj = unserialize($_SESSION['user']);
$username = $userobj->username;

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

$include =  pg_fetch_row($results);
$include = $include[0];

error_log("include".$include);

// =========================================================
// getting previus user responces 
// =========================================================


$query = 'SELECT * FROM load_user_responces WHERE username=$1';
$results = pg_query_params($query, array($username));
    
if (pg_num_rows($results) == 0) 
{
    error_log("No previous entries found"); // users who just registered do not have any user 


    echo json_encode(array(  
    "affiliation"=> "",    // How are you affiliated with RPI?
    "arch_edu"=>"",        // Years of formal education in architecture?
    "visual_edu"=>"",      // Years of formal education in visual arts?           
    "arch_exp"=>"",        // Years of job experience in architecture? (including internships)
    "visual_exp"=>"",      // Years of job experience in visual arts? (including internships)
    "software_list_str"=>"",// Have you used any of the following modeling software?
    "other_software"=>"",  // Have you used any other unlisted modeling software?
    "software_exp"=>"",    // Years of experience with modeling softwares?
    "other_exp"=>"",      // Other relevant education or experiences? 
    "user_email"=>"",      // Is it okay if we follow up questions about model's created in our system?
    "include"=>$include
    ));   

    error_log( 'load_get_feedback.php end' );
    
    die();

}

if (pg_num_rows($results) > 1) 
{
    error_log( 'More than 1 entry saved in database' );
}

$row = pg_fetch_row($results);

error_log("passed this point");


echo json_encode(array(  
	"rpi_affilate" => $row[1],  // Are you affiliated with Rensselaer Polytechnic Institute?
    "affiliation"=> $row[2],    // How are you affiliated with RPI?
    "arch_edu"=>$row[3],        // Years of formal education in architecture?
    "visual_edu"=>$row[4],      // Years of formal education in visual arts?           
    "arch_exp"=>$row[5],        // Years of job experience in architecture? (including internships)
    "visual_exp"=>$row[6],      // Years of job experience in visual arts? (including internships)
    "software_list_str"=>$row[7],// Have you used any of the following modeling software?
    "other_software"=>$row[8],  // Have you used any other unlisted modeling software?
    "software_exp"=>$row[9],    // Years of experience with modeling softwares?
    "other_exp"=>$row[10],      // Other relevant education or experiences? 
    "color_blind"=>$row[11],    // Are you colorblind? (optional)
    "user_email"=>$row[12],      // Is it okay if we follow up questions about model's created in our system?
    "include"=>$include
));   

error_log( 'load_get_feedback.php end' );
?>