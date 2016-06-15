<?php
//echo "<script>alert('*')</script>";
session_start();
require_once('config.inc.php');          // Connect to DB

$data_array = array();
$view_path = $_SESSION['view_path'];
echo json_encode(array("result"=>$view_path));

/*
$sql = "SELECT suffix FROM lookup WHERE path=$1";
$res = pg_query_params($sql,array($view_path));

$start = 0;
$end = $start+6;


//check to see that the suffix has not been repeated
$spath = substr(hash('sha256',$view_path),$start,$end);
$sql2 = "SELECT path FROM lookup WHERE suffix=$1";
$res2 = pg_query_params($sql2,array($spath));
//should be 0
while(pg_num_rows($res2)>1){
    $start++;
    $end++;
    
    $spath = substr(hash('sha256',$view_path),$start,$end);
    $res2 = pg_query_params($sql2,array($spath));
    
    if($end == 64)
        break;
}



//check to see if path exists for given hash
if(pg_num_rows($res)>0){
    $spath = pg_fetch_row($res);
    $spath = $spath[0];
}
else {
    $sql = "INSERT INTO lookup VALUES ($1,$2)";
    pg_query_params($sql,array($spath,$view_path));
}

echo json_encode(array("result"=>$spath));

 */
?>
