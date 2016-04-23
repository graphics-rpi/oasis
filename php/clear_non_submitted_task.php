
<?php
// Get Session and clear our session varibles
session_start();
error_log('clear_non_submitted_task: cleared task_container session varible');
$_SESSION['task_container'] = "";
?>

