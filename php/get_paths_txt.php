<?php
require_once('config.inc.php');          // Connect to DB

$paths_txt = "";
$id = $_GET['id'];
$sql = "SELECT paths_txt FROM model_data WHERE id=$1";
$res = pg_query_params($sql, array($id));

$data = pg_fetch_row($res);
$paths_txt = $data[0];

# Sending to output_viewer_aux.js
echo json_encode(array("paths_txt" => $paths_txt));

?>