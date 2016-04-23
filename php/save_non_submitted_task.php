<?php
// Get Session and save this to our session varible
session_start();
error_log('save_non_submitted_task: Set task_container session varible');
$task_container = $_POST['json'];
error_log('save_non_submitted_task: '.$task_container);
$_SESSION['task_container'] = $task_container;
?>

