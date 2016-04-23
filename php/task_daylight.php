
<?php
error_log("task_daylight.php: Start");

session_start();
require_once('config.inc.php');          
require_once('user.php');
require_once('model.php');

// Get user object and username and session model
$userobj       = unserialize($_SESSION['user']);
$username      = $userobj->username;
$session_model = $userobj->workingModel;
$id            = $session_model->id;
$t_called      = $_POST['t_called'];
$t_wait        = "";
$t_ran         = "";


// Hashed version of the users folder name
$user_folder_name  = $userobj->getUserFolderName();
$user_folder_path  = '/var/www/user_output/'.$user_folder_name.'/';
$model_folder_path = $user_folder_path.'model_'.$id.'/';

// Results folder
$res_folder_path   = $model_folder_path.'results/';

// ===================================================
// Creating the simulation folder
// ===================================================
$month    = str_pad( $_POST['month'  ], 2, "0", STR_PAD_LEFT);
$day      = str_pad( $_POST['day'    ], 2, "0", STR_PAD_LEFT);
$date     = $month."/".$day;

$hour     = str_pad( $_POST['hour'   ], 2, "0", STR_PAD_LEFT);
$minute   = str_pad( $_POST['minute' ], 2, "0", STR_PAD_LEFT);
$time     = $hour.":".$minute;

$tz_sign     = $_POST['tz_sign'];
$tz_hr       = str_pad( $_POST['tz_hr'   ], 2, "0", STR_PAD_LEFT);
$tz_min      = str_pad( $_POST['tz_min'  ], 2, "0", STR_PAD_LEFT);
$timezone    = $tz_sign.":".$tz_hr.":".$tz_min;

$weather  = $_POST['weather'];

$sim_folder_name = "sim_%s%s_%s%s_%s%s%s_%s";
$sim_folder_name = sprintf($sim_folder_name,$month,$day,$hour,$minute,$tz_sign,$tz_hr,$tz_min,$weather);
$args = "%s%s_%s%s_%s%s%s_%s";
$args = sprintf($args,$month,$day,$hour,$minute,$tz_sign,$tz_hr,$tz_min,$weather);

pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
  date("Y-m-d").'|'.date("h:i:sa"),
  $username,
  $id,
  "task_daylight.php",
  $sim_folder_name
));

// Creating two folders, one for normal color visual other for fcv
$sim_inout_ncv   = $res_folder_path.$sim_folder_name.'_ncv/';
$sim_inout_fcv   = $res_folder_path.$sim_folder_name.'_fcv/';

$floor_texture_ncv = $sim_inout_ncv.'surface_camera_floor_0_0_texture.png';
$floor_texture_fcv = $sim_inout_fcv.'surface_camera_floor_0_0_texture.png';

// If either ncv or fcv do not have a texture, rerun them
if( !file_exists($floor_texture_ncv) or !file_exists($floor_texture_fcv)){

  // Create output folder + task file only if we need to
  if ( mkdir($sim_inout_ncv) and mkdir( $sim_inout_fcv ) )
  {
    // we just created these folders above
  }
  else
  {
    // cleaning the exisiting folder
    shell_exec( 'rm '.$sim_inout_ncv.'*' );
    shell_exec( 'rm '.$sim_inout_fcv.'*' );
  }

  // ===================================================
  // Preparing the ncv simulation folder with inputs
  // ===================================================
  $slow_dir  = $model_folder_path.'slow/';
  $tween_obj = $model_folder_path.'tween/foo.obj';

  // We want to copy all files in slow folder into our sim folders
  shell_exec( 'cp '.$slow_dir.'* '.$sim_inout_ncv );
  shell_exec( 'cp '.$slow_dir.'* '.$sim_inout_fcv );


  // ===================================================
  // Creating the task file for this
  // ===================================================

  // Location where wall and task folder are located
  $lsvo_path = '/var/www/user_task/lsvo/';

  // ===================================================
  // Creating our two task files
  // ===================================================

  // Pad our identifier so sorting happens naturally
  $identifier = time();
  $task_file = fopen($lsvo_path.$identifier.'ncv.task', "w"); 
  $task_file_contents = $sim_inout_ncv."\n";
  $task_file_contents = $task_file_contents.$month."\n";
  $task_file_contents = $task_file_contents.$day."\n";
  $task_file_contents = $task_file_contents.$hour."\n";
  $task_file_contents = $task_file_contents.$minute."\n";
  $task_file_contents = $task_file_contents.$tz_sign."\n";
  $task_file_contents = $task_file_contents.$tz_hr."\n";
  $task_file_contents = $task_file_contents.$tz_min."\n";
  $task_file_contents = $task_file_contents.$weather."\n";
  $task_file_contents = $task_file_contents."ncv\n";
  fwrite($task_file, $task_file_contents);

  $identifier = time();
  $task_file = fopen($lsvo_path.$identifier.'fcv.task', "w"); 
  $task_file_contents = $sim_inout_fcv."\n";
  $task_file_contents = $task_file_contents.$month."\n";
  $task_file_contents = $task_file_contents.$day."\n";
  $task_file_contents = $task_file_contents.$hour."\n";
  $task_file_contents = $task_file_contents.$minute."\n";
  $task_file_contents = $task_file_contents.$tz_sign."\n";
  $task_file_contents = $task_file_contents.$tz_hr."\n";
  $task_file_contents = $task_file_contents.$tz_min."\n";
  $task_file_contents = $task_file_contents.$weather."\n";
  $task_file_contents = $task_file_contents."fcv\n";
  fwrite($task_file, $task_file_contents);



  // ===========================================
  // putting inside the daylighting_task_table
  // ===========================================
  $cmd = 'INSERT INTO daylighting_task_table VALUES($1,$2,$3,$4,$5,$6,$7,$8,$9)';
  pg_query_params($cmd,array($t_called,$t_wait,$t_ran,$id,$date,$time,$timezone,$weather,$args));



}

// ========================================
// Setting global view path
// ========================================
// $_SESSION['view_path'] = '../user_output/'.$user_folder_name.'/'.'model_'.$id.'/results/'.$sim_folder_name.'/';
// $_SESSION['view_path'] = '128.213.17.82/'.$user_folder_name.'/'.'model_'.$id.'/'.$sim_folder_name.'/';
error_log("task_daylight.php: Done");
?>
