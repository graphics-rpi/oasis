<?php
	
// This php script will connect and recive questions from the daylighting postgres server
// It will also invoke a call load_responces if these questions have been answered before
// The output of this script will be generated html code that will be used to populate the
// feedback pane in our webpage via AJAX calls.

// Connect to the session	
session_start();
require_once('config.inc.php');
require_once('user.php');
require_once('model.php');


// Getting user object and username
$userobj  = unserialize($_SESSION['user']);
$username = $userobj->username;

// Get the tab from the URL 
$tab = $_GET["tab"];

// Update cookie so it knows what tab its on
$_SESSION["tab"] = $tab;

// Search though the feedback_question table to get questions that are in this tab
$results = pg_query_params('SELECT qid, question, type FROM feedback_questions WHERE tab=$1', array($tab));

// Where we will be storing all the questions to be 
$package_questions = array();

// For all questions found for this tab display them
while($cur_row = pg_fetch_row($results) ){

	$qid 			= $cur_row[0];
	$question 		= $cur_row[1];
	$type 			= $cur_row[2];
	$responce 		= "NaN";
	
	// Did this user answer this question before?
	$debug_str = 'SELECT responce FROM feedback_responces WHERE qid='.$qid.' AND username=\''.$username.'\' ;';
	$responce_result = pg_query($debug_str);
	// error_log("MAX: $debug_str");
	
	$num_questions = pg_num_rows($responce_result);
	
	// error_log("MAX: Number of questions: $num_questions", 0);
	
	if ( pg_num_rows( $responce_result ) > 0 ) {
		
		// Answered previous so update responce
		$responce_row = pg_fetch_row($responce_result);
		$responce = $responce_row[0];
		// error_log("RESPONCE READ: $responce",  0);
	}
	
	// Saving this into our array of questions
	$pkg_question = array( 'qid' => $qid , 'question' => $question,'tab' => $tab , 'type' => $type, 'responce' => $responce );
	array_push($package_questions,$pkg_question);

}

// Send off our packaged 2D array of questions to client land
echo json_encode($package_questions);


?>


