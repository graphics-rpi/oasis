<?php
$data_array = array();
$path = $_POST["spath"];

$view_path = substr(hash('sha256', $path),0,6);

echo json_encode(array("result"=>$view_path));
?>
