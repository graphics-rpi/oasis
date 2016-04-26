<?php

require_once('config.inc.php');          // Connect to DB

$username = $_POST['usr'];
$sql = "SELECT user_model_num FROM model_meta WHERE username=$1";
$res = pg_query_params($sql, array($username));

$user_models = array();

while ( $row = pg_fetch_row($res) ){

	if( !in_array($row[0], $user_models)){
		array_push($user_models, $row[0]);
	}
}

# Sending to output_viewer_aux.js
echo json_encode(array("data" => $user_models));

?>