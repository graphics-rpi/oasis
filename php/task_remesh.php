<?php
error_log("task_remesh.php: Start");

session_start();
require_once('config.inc.php');          
require_once('user.php');
require_once('model.php');

// Location where wall and task folder are located
$remesh_path = '/var/www/user_task/remesh/';

// inputs into remesh_task_table
$t_called = $_POST['t_called'];

// ========================================
// Creating the identifer
// ========================================

// Count the current number of task in system
// $count = 0; 
// $dir = $remesh_path.'task/';
// if ($handle = opendir($dir)) {
//   while (($file = readdir($handle)) !== false){
//     if (!in_array($file, array('.', '..')) && !is_dir($dir.$file)) 
//       $count++;
//   }
// }

// Pad our identifier so sorting happens naturally
$identifier = time();

// ========================================
// Creating user model folder
// ========================================

// Get user object and username and session model
$userobj       = unserialize($_SESSION['user']);
$username      = $userobj->username;
$session_model = $userobj->workingModel;
$id            = $session_model->id;

// Get the folder where we 
$model_folder_path = '/var/www/user_output/geometry/'.$id.'/';

// Create this folder if it doesn't exisit, if it does then we have already
// Created a slow and tween folder and have a view (no change)
if (!is_dir($model_folder_path))
{
  mkdir($model_folder_path, 0755, true);
}
  // Create subfolders where we will store simulation results
  mkdir($model_folder_path.'slow/');
  mkdir($model_folder_path.'tween/');

  // ========================================
  // Creating task file and wall file
  // ========================================


  //Create remesh_path if it doesn't exist
  if(!is_dir($remesh_path))
  {
    mkdir($remesh_path, 0755, true);
  }
  if(!is_dir($remesh_path.'task/'))
  {
    mkdir($remesh_path.'task/', 0755, true);
  }
  if(!is_dir($remesh_path.'wall/'))
  {
    mkdir($remesh_path.'wall/', 0755, true);
  }
  // Create them at the same time
  $task_file = fopen($remesh_path.'task/'.$identifier.'.task', "w");
  $wall_file = fopen($remesh_path.'wall/'.$identifier.'.wall', "w");

  // The task file contains the args for run_remesh.sh command
  $task_file_content  = $remesh_path.'wall/'.$identifier.".wall\n"; # Input file
  $task_file_content  = $task_file_content.$model_folder_path;                         # Output folder
  fwrite($task_file,$task_file_content);

  // We will write the wallfile text here iin the task folder
  $wallfile_txt = $session_model->wallfile_txt;
  fwrite($wall_file,$wallfile_txt);

  // ========================================
  // DEBUG: Forcing run remesher to run
  // ========================================

  $wall_arg = $remesh_path.'wall/'.$identifier.".wall";
  $out_arg  = $model_folder_path;
  error_log("task_remesh.php: wall_arg:".$wall_arg);
  error_log("task_remesh.php: out_arg:".$out_arg);

  chdir('/var/www/bin/');

  $t_wait = time() - $t_called; // how long we waited for this script to run

  $t_before = time();

  $log = shell_exec('./run_remesher.sh '.$wall_arg.' '.$out_arg);

  shell_exec('rm /var/www/user_task/remesh/wall/'.$identifier.'.wall');
  shell_exec('rm /var/www/user_task/remesh/task/'.$identifier.'.task');
  $t_ran =  time() - $t_before;

  $log_file = fopen($model_folder_path.'error.log', "w");
  fwrite($log_file, $log);

  // ========================================
  // Putting into lsvo_render_responces
  // ========================================
  $cmd =  'INSERT INTO remesh_task_table VALUES($1,$2,$3,$4)';
  pg_query_params($cmd,array($t_called,$t_wait,$t_ran,$id));

// ========================================
// Setting global view path
// ========================================
$_SESSION['view_path'] = '../user_output/geometry/'.$id.'/slow/';


// ========================================
// Putting into error_table
// ========================================
pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
  date("Y-m-d").'|'.date("h:i:sa"), // YYMMDD|HHMMSS
  $username,                        // username
  $id,                              // id (model)
  "task_remesh.php",                // script
  ""                                // args
));

error_log("task_remesh.php: Done");

?>
