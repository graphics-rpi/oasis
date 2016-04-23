
<?php

// Get Session
session_start();
require_once('config.inc.php');          // Connect to DB
require_once('user.php');
require_once('model.php');

$id = $_POST["id"];
$sql = "SELECT title FROM model_meta WHERE id=$1";

$res = pg_query_params($sql, array($id));
$row = pg_fetch_row($res); 
$row = $row[0];

error_log('pg_fetch: '.$row );
//echo "<script>console.log(".$row.");</script>";
// send off json obj
echo json_encode(array("data" =>  $row));

?>
