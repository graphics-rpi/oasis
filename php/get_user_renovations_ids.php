<?php
require_once('config.inc.php');          // Connect to DB

$paths_txt = "";
$umn = intval($_POST['umn']);
$usr = $_POST['usr'];
$sql = "SELECT id FROM model_meta WHERE username=$1 AND user_model_num=$2 ORDER BY user_renov_num ASC";
$res = pg_query_params($sql, array($usr,$umn));

# Where we will store our information
$renov_list =  array();

while($data = pg_fetch_row($res))
{
  array_push($renov_list, $data[0]);
}


# Sending to output_viewer_aux.js
echo json_encode(array("data" => $renov_list));

?>
