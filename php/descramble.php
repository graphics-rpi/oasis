<?php

session_start();
require_once('config.inc.php');          // Connect to DB

$data_array = array();
$spath = $_POST["spath"];

$sql = "SELECT path FROM lookup WHERE suffix=$1";
$res = pg_query_params($sql,array($spath));

$view_path = "";

if(pg_num_rows($res)>0){
    $view_path = pg_fetch_row($res);
    $view_path = $view_path[0];
}
else {
    echo "ERROR ERROR!!!";
}

echo json_encode(array("result"=>$view_path));

?>
