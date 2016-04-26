<?php

require_once('config.inc.php');          // Connect to DB

$sql = "SELECT email FROM users;";
$res = pg_query($sql);

$all_users = array();

while ( $row = pg_fetch_row($res) ){
	array_push($all_users, $row[0]);
}

# Sending to output_viewer_aux.js
echo json_encode(array("data" => $all_users));

?>