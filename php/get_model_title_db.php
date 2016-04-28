<?php

require_once('config.inc.php');          // Connect to DB

$username = $_POST['usr'];
$user_model_num = $_POST['umn'];

$sql = "SELECT title FROM model_meta WHERE username=$1 AND user_model_num=$2";
$res = pg_query_params($sql,array($username,$user_model_num));

$title = "";

$row = pg_fetch_row($res);

echo json_encode(array("data" => $row[0]));

?>
