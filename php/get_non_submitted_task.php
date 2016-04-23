
<?php
// Get Session and clear our session varibles
session_start();
$task_container = $_SESSION['task_container'];
error_log('get_non_submitted_task: get task_container session varible: '.$task_container);

if( $task_container != "")
{
  echo json_encode(array("data" => $task_container ));
}
else
{
  echo json_encode(array("data" => "" ));
}
?>
