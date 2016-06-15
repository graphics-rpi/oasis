<?php
// A class that will be used to query if we have a valid user
class User
{
  
  // Fields
  var $username, $password, $workingModel, $tab;
  
  // Memeber Functions
  function getName()
  {
    return $this->username;
  }
  
  function User($username, $password)
  {
    // We get input, which could be bad malicious iputs
    // We encode the password and scrub input
    $this->username = filter_var($username, FILTER_SANITIZE_STRING);
    
    // This is used for hash passwords
    require_once("lib/password.php");
    $this->password = sha1($password);
    
    $this->tab = "tab-sketch";
  }
  
  function register_user($include_in_study)
  {
    // Assumption: User not in DB
    
    // Open connection
    require_once("config.inc.php");
    
    $username = $this->username;
    $password = $this->password;

    if($include_in_study){
      $include = "true";
    }else{
      $include = "false";
    }

    if (strlen($username) == 0)
    {
      echo "<h1>Error:</h1>";
      echo "Empty or malicious data is trying to be passed";
      exit;
    }
    
    // insert
    pg_query_params('INSERT INTO users VALUES($1,$2,$3)', array(
      $username,
      $password,
      $include
    ));

    pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
      date("Y-m-d").'|'.date("h:i:sa"),
      $username,
      "",
      "register_user",
      ""
    ));

  } //register_user
  
  function exisiting_user()
  {
    // Will hopefully allow me to connect to loginDB
    require_once("config.inc.php");
    
    // Here is where we access the database.
    $username = $this->username;
    
    
    // Is the Query I want to ask the loginDB
    $results = pg_query_params('SELECT email, password FROM users WHERE email=$1', array(
      $username
    ));
    
    if (pg_num_rows($results) > 0)
    {
      
      return true;
      
    }
    else
    {
      
      return false;
      
    }
  } //exisiting_user
  
  function authenticate()
  {
    // Will hopefully allow me to connect to loginDB
    require_once("config.inc.php");
    
    // Here is where we access the database.
    $username = $this->username;
    $password = $this->password;
    
    // Is the Query I want to ask the loginDB
    // Trying to prevent sql injects
    $results = pg_query_params('SELECT email, password FROM users WHERE email= $1 AND password= $2;', array(
      $username,
      $password
    ));
    
    if (pg_num_rows($results) > 0)
    {
      
      // Sucessful Login!
      $_SESSION['user'] = serialize($this);

      
      return true;
      
    }
    else
    {
      
      // Failure
      $_SESSION['error'] = "Bad username or password";
      return false;
      
    }
  } //auth
  
  function getUserFolderName()
  {
    // NOTE: No longer used as of the spring 2016 refactor

    // // First part of the folder name
    // $username     = $this->username;
    // $dirName      = $username;
    // return substr(sha1($dirName), 0, 16);
    
  }
  
  function createUserFolder()
  {
    // NOTE: No longer used as of the spring 2016 refactor

    // // In this function we will create a folder that will
    // // hold in all the slow, tween, and results
    // $dir_name = $this->getUserFolderName();
    
    // // If we manage to create a folder (doesn't exisit)
    // if (mkdir("../user_output/" . $dir_name))
    // {
    //   shell_exec('chmod 0775 ../user_output/' . $dir_name);
    //   error_log( 'Created user folder: ../user_output/'.$dir_name );
    // }
    // else
    // {
    //   error_log( 'Folder Already Exisit: ../user_output/'.$dir_name );
    // }

  } //create
  
  function loadLastModel()
  {
    // Imports
    require_once('config.inc.php'); // Connect to DB
    require_once('model.php');
    
    $username = $this->username;
    
    $query = "SELECT last_model_id FROM users WHERE email='$username';";
    $results = pg_query($query) or die('Failed to get back last id');
    
    
    if (pg_num_rows($results) == 0)
    {
      die(" Did not sign up / register ");
      
    }
    
    $uniqueId = null;
    while ($row = pg_fetch_row($results))
    {
      $uniqueId = $row[0];
    }
    
    if ($uniqueId == -1)
    {
      // We have a user that didn't make a model yet
      // In save_model.php we will only increment the unquie_model id
      // by one unit.
      
      $model_obj = new Model(NULL, -1, NULL, NULL, NULL);
      
      $this->workingModel = $model_obj;
      
      return;
    }
    
    // Get Model Number
    $modelNum = $this->pull_modelNum_db($uniqueId);
    
    $renovationNum = $this->pull_renovationNum_db($uniqueId);
    
    $wallTxt = $this->pull_wall_db($uniqueId);
    
    $transTxt = $this->pull_tran_db($uniqueId);
    
    $model_obj = new Model($uniqueId, $modelNum, $renovationNum, $wallTxt, $transTxt);
    
    // Loaded
    $this->workingModel = $model_obj;
  }
  
  function loadModel($what_model, $what_rennovation)
  {
    die('unimplemented');
  } // TODO Implement this 
  
  
  function pull_wall_db($id)
  {
    // This function will return the wall text from the db based only on the id
    // This function is used by loadModel
    
    require_once('config.inc.php'); // Connect to DB
    
    $wall_text = ""; // File we are going to return
    
    $query = "SELECT wall_file_contents FROM models WHERE model_id='$id';";
    $results = pg_query($query) or die('Failed to back wall file');
    
    if (pg_num_rows($results) == 0)
    {
      die(' No Results for Wall file ');
    }
    
    while ($row = pg_fetch_row($results))
    {
      $wall_text = $row[0];
    }
    return $wall_text;
  } //pull_wall_db
  
  
  function pull_tran_db($id)
  {
    require_once('config.inc.php'); // Connect to DB
    
    $trans_text = "";
    
    $query = "SELECT trans_file FROM transforms WHERE id='$id';"; // File we are going to return
    
    $results = pg_query($query) or die('Failed to back wall file');
    
    if (pg_num_rows($results) == 0)
    {
      die(' No Results for Wall file ');
    }
    
    // Get that wall text
    while ($row = pg_fetch_row($results))
    {
      $trans_text = $row[0];
    }
    
    return $trans_text;
  } //pull_tran_db
  
  function pull_modelNum_db($id)
  {
    require_once('config.inc.php'); // Connect to DB
    
    $modelNum_query = "SELECT user_model_number FROM models WHERE model_id='$id';";
    
    $modelNum_result = pg_query($modelNum_query) or die('Failed to get modelNum');
    if (pg_num_rows($modelNum_result) == 0)
    {
      die(" Failed to get ModelNum");
    }
    $modelNum = null;
    while ($row = pg_fetch_row($modelNum_result))
    {
      $modelNum = $row[0];
    }
    
    return $modelNum;
  } //pull
  
  function pull_renovationNum_db($id)
  {
    require_once('config.inc.php'); // Connect to DB
    
    $renovationNum_query = "SELECT renovation_of_model FROM models WHERE model_id='$id';";
    $renovationNum_result = pg_query($renovationNum_query) or die('Failed to get renonum');
    if (pg_num_rows($renovationNum_result) == 0)
    {
      die(" Failed to get renovnum");
    }
    $renovationNum = null;
    while ($row = pg_fetch_row($renovationNum_result))
    {
      $renovationNum = $row[0];
    }
    
    return $renovationNum;
  } // pull
  
  /* This function will be used to get new model ids */
  function getNewUniqueModelId()
  {
    require_once('config.inc.php'); // Connect to DB
    $query = "SELECT * FROM models WHERE username='$this->username';"; // Get all entries in table
    $result = pg_query($query) or die('Failed to get all models');
    
    $new_unquie_id = pg_num_rows($result); // Up by 1
    
    $new_unquie_id = (string) $new_unquie_id;
    $username      = $this->username;
    
    return $username . "-" . $new_unquie_id;
    // encrypt username a string
    //return $new_unquie_id;
  } //newId
  
  function clear_user_folders()
  {
    $dir_name = $this->getFolderName();
    
    // Clear those folders 
    exec("rm -r ../user_output/" . $dir_name . "/slow/* ");
    exec("rm -r ../user_output/" . $dir_name . "/tween/*");
    exec("rm -r ../user_output/" . $dir_name . "/wall/* ");
  } //clear
  
  /* Runs the scripts + generates the data on temp folder */
  function create_render_files()
  {
    // REQUIRED: CHECKS
    if (!isset($this->workingModel))
    {
      die('Fetal Error: Working Model Not Set');
    }
    
    $workingModel = $this->workingModel;
    
    if (!isset($workingModel->uniqueID, $workingModel->modelNum, $workingModel->renovationNum, $workingModel->wallTxt, $workingModel->transTxt))
    {
      print_r($workingModel);
      die('Working Model fields not set');
    }
    
    // Clear those folders 
    $this->clear_user_folders();
    
    // Get Path
    $user_path = "/var/www/user_output/" . $this->getFolderName() . '/';
    
    
    // Overview of what this function does
    // 1) Dumps wall/trans file in ../user_output/username/wall/
    // 2) Runs script
    
    // Create the wall and trans file within the wall folder
    $wall_handler  = fopen($user_path . 'wall/model.wall', 'w');
    $trans_handler = fopen($user_path . 'wall/model.trans', 'w');
    
    fwrite($wall_handler, $workingModel->wallTxt);
    fwrite($trans_handler, $workingModel->transTxt);
    
    fclose($wall_handler);
    fclose($trans_reader);
    
    shell_exec('chmod 0775 ' . $user_path . 'wall/model.wall');
    shell_exec('chmod 0775 ' . $user_path . 'wall/model.trans');
    
    // Run Script
    // <script> <wallfile> <output_path> <month> <time>
    $path_to_script = "../bin/run_remesher_and_lsvo.sh";
    $path_to_wall   = $user_path . 'wall/model.wall';
    $path_to_output = $user_path . '';
    $lsvo_month     = -1;
    $lsvo_hour      = -1;
    
    // Get hour and month
    $trans_reader = fopen($user_path . 'wall/model.trans', 'r');
    while ($line = fgets($trans_reader))
    {
      $words = explode(" ", $line);
      if ($words[0] == 'month')
      {
        $lsvo_month = (int) $words[1];
      }
      if ($words[0] == 'hour')
      {
        $lsvo_hour = (int) $words[1];
      }
    } //while
    fclose($trans_reader);
    
    // Execute script
    $log = shell_exec($path_to_script . " " . $path_to_wall . " " . $path_to_output . " " . $lsvo_month . " " . $lsvo_hour);
    
    // Ready logs
    $log_handler = fopen($user_path . 'wall/model.log', 'w');
    fwrite($log_handler, $log);
    shell_exec('chmod 0775 ' . $user_path . 'wall/model.log');
  } //render
  
  // Will handle users logout by clearing their folder
  function logout()
  {
    $dir_name = $this->getFolderName();
    shell_exec("rm -rf ../user_output/" . $dir_name);
  } //logout
  
  // This function generates a string that has all titles
  // of previous models and their unquie ids as js variables
  // <ul>
  // <li><a href="#"> Titles </a></li>
  // <li><a href="#"> Titles </a></li>
  // <li><a href="#"> Titles </a></li>
  // </ul>

  function prevModelList()
  {
    require_once('config.inc.php'); // Connect to DB
    $username = $this->username; // Get username

    // Query the database for models made by users
    $cmd = "SELECT * FROM model_meta WHERE username='$username' ORDER BY user_model_num, user_renov_num DESC;";
    error_log("CMD: ".$cmd);
    $res = pg_query($cmd) or die('Failed to get previous models');
    
    // init setup
    $prev_model_num = -1;
    $unique_id_arr = array();
    $title_arr     = array();
    
    // looping to collect titles and unquie_ids
    while ($data = pg_fetch_row($res))
    {
      
      if( $prev_model_num != $data[3] )
      {
        array_push($unique_id_arr, $data[0]);
        array_push($title_arr, $data[1]);
        $prev_model_num = $data[3];
      }

    }
    
    // Generate the html string
    if (count($title_arr) > 0)
    {
      $generated_html = '<a class="pure-menu-heading">Previous Models</a><ul>';
    }
    else
    {
      $generated_html = '<a class="pure-menu-heading">No Previously Created Models, Click the <b>New Model</b> button above to start!</a><ul>';

      // Will make a call to first time user
      $generated_html = $generated_html."<script>first_time_user_prompt();</script>";

    }
    
    for ($i = count($title_arr)-1; $i >= 0; --$i)
    {
      $current_item_fmt = '<li><a onclick=\'load_previous_model("%s");\' href="javascript:void(0);">%s</a></li>';
      $current_item = sprintf($current_item_fmt, $unique_id_arr[$i], $title_arr[$i]);
      // $current_item = '<li><a onclick=\'load_previous_model('.$unique_id_arr[ $i ].');\' href="javascript:void(0);">' . $title_arr[$i] . '</a></li>';
      
      $generated_html = $generated_html . $current_item;
    }
    $generated_html = $generated_html . "</ul>";
    

    return $generated_html;
  }

  //Returns an array representing the tuple inserted into the database.
  //row[0] = id, row[1] = title, row[2] = username, row[3] = user_model_num, row[4] = user_renov_num
  function save_to_database()
  {	   
    $title 				= $this->workingModel->title;
    $userModelNumber 	= $this->workingModel->user_model_num;
    $wallfile 			= $this->workingModel->wallfile_txt;
    $paths 				= $this->workingModel->paths_txt;

    $sqlCmd = "SELECT * FROM insert_model($1, $2, $3, $4, $5);";
    error_log('sql_cmd: '.$sqlCmd);

    $insertedModel = pg_query_params($sqlCmd, array($title, $this->username, $userModelNumber, $wallfile, $paths)) or die("Failed to save to model to database");

    if(pg_num_rows($insertedModel) == 1)
    {
    	$row = pg_fetch_row($insertedModel);
    	$this->workingModel->id = $row[0];
    	$this->workingModel->title = $row[1];
    	
    	$this->workingModel->user_model_num = $row[3];
    	$this->workingModel->user_renov_num = $row[4];
    }

	pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
      date("Y-m-d").'|'.date("h:i:sa"),
      $username,
      "",
      "save to database end",
      ""
    ));
  }

  function sortByCreationTime($file_1, $file_2)
  {
    $file_1t = (string)filectime($file_1);
    $file_2t = (string)filectime($file_2);
    if($file_1t == $file_2t)
    {
        return 0;
    }
    return $file_1t > $file_2t ? 1 : -1;
  }

  // Will go through user simulations and return their url paths and parsed data
  // in the form of an array
  function get_user_simulations()
  {

    error_log("Start");
    $simulations = array();

    // Mindy
    // Step 1: Get list of all ids that belong to this user
    require_once("config.inc.php"); // let us connect to database
    $model_id_list = array();
    $sql = "SELECT id FROM model_meta WHERE username=$1";
    $results = pg_query_params($sql,array($this->username));

    while($cur_row = pg_fetch_row($results) )
    {

      if( is_dir("/var/www/user_output/texture/".$cur_row[0]."/") )
      {
          array_push($model_id_list,$cur_row[0]);
      }
    }
    
    // Step 2: For each of those traverse /var/www/user_output/<id>/ for simulations

    foreach($model_id_list as $cur_id)
    {

      // Get all sim folders under this model
      $results_sim = scandir("/var/www/user_output/texture/".$cur_id."/");

      // For each folder toss into simulations if it is an actual sim folder
      foreach ($results_sim as $sim) 
      {
        if( $sim === '.' or $sim === '..')
        {
          // exclude this and parent from search
          continue;
        }

        if( is_dir("/var/www/user_output/texture/".$cur_id."/".$sim."/") )
        {

          //sim_mmdd_hhmm_weather_ssmmhh_fcv
          // we want only the normal ones to show
          $vis_mode = explode("_", $sim);
          $vis_mode = $vis_mode[5];
          if( $vis_mode == "ncv"  ){
            // Step 3: Push absolute path into simulations array
            array_push($simulations,'../user_output/texture/'.$cur_id.'/'.$sim.'/');
          }
        }
      }
    }
    
    
    // Step 4: Continue business as usual

    /*
    // Hashed version of the users folder name
    $user_folder_name  = $this->getUserFolderName();
    $user_folder_path  = '/var/www/user_output/'.$user_folder_name.'/';


    $simulations = array();

    $results = scandir($user_folder_path);

    foreach ($results as $model_folder) 
    {

      // Not folders
      if ($model_folder === '.' or $model_folder === '..')
      {
        continue;
      }

      // This is a model folder
      if (is_dir($user_folder_path . '/' . $model_folder)) 
      {

        $results_sim = scandir($user_folder_path . $model_folder.'/results/');

        foreach ($results_sim as $sim) 
        {
          if( $sim === '.' or $sim === '..')
          {
            continue;
          }

          // This is a simulation folder
          if( is_dir($user_folder_path.$model_folder.'/results/'.$sim.'/') )
          {

            //sim_mmdd_hhmm_weather_ssmmhh_fcv
            // we want only the normal ones to show
            $vis_mode = explode("_", $sim);
            $vis_mode = $vis_mode[5];
            if( $vis_mode == "ncv"  ){
              array_push($simulations,'../user_output/'.$user_folder_name.'/'.$model_folder.'/results/'.$sim.'/');
            }
          }
        }
      }
    }

    */

    $model_id = array();
    $month    = array();
    $day      = array();
    $hour     = array();
    $minute   = array();
    $tz_sign  = array();
    $tz_hour  = array();
    $tz_minute  = array();
    $weather  = array();
    $status   = array(); // string with either 'success','pending','error'
    $ctime    = array(); //creation time

    usort($simulations, "User::sortByCreationTime");

    // Now we will make sense of these paths
    foreach( $simulations as $simulation_path )
    {
      // Breaking into pieces 
      $pieces = explode("/",$simulation_path);

      // getting model id
      $id = $pieces[3]; 

      array_push($model_id, $id);

      $sim_var = $pieces[4];
      $sim_var = explode("_",$sim_var); // sim,mmdd,hhmm,ssmmhh,weather

      $date = $sim_var[1]; //mmdd
      $mon    = intval( substr($date,0,2));
      $dy     = intval( substr($date,2,2));

      array_push($month, $mon);
      array_push($day,   $dy);

      $time = $sim_var[2]; //hhmm
      $hr    = intval( substr($time,0,2));
      $mi    = intval( substr($time,2,2));

      array_push($hour, $hr);
      array_push($minute,   $mi);

      $timezone_str = $sim_var[3]; //-1hhmm or 1hhmm

      if(strlen($timezone_str) == 6){

        $tz_s     = intval( substr($timezone_str,0,2));
        $tz_hr    = intval( substr($timezone_str,2,2));
        $tz_mi    = intval( substr($timezone_str,4,2));
      }else{

        $tz_s     = intval( substr($timezone_str,0,1));
        $tz_hr    = intval( substr($timezone_str,1,2));
        $tz_mi    = intval( substr($timezone_str,3,2));
      }

      array_push($tz_sign,   $tz_s);
      array_push($tz_hour,   $tz_hr);
      array_push($tz_minute, $tz_mi);

      $w = $sim_var[4];

      array_push($weather, $w);

      // Here we are doing some naive error checking

      // Where we choose if we have PENDING, ERROR, VIEW 
      // error_log("get_user_simulations: ".$simulation_path.'error.log');


      //if ( file_exists($simulation_path.'pending.log') )
      if (file_exists($simulation_path.'pending.lock') )
      {
        array_push($status,"running");
      }
      else
      {

        if( !file_exists($simulation_path.'complete.lock') )
        {
            array_push($status, "pending");
        }
        else
        {

          if( file_exists($simulation_path.'surface_camera_floor_0_0_texture.png'))
          {
            array_push($status, "success");
          }
          else
          {
            array_push($status, "error");
          }

        }
      }

      array_push($ctime, filectime($simulation_path));
    }

    $data = array();
    array_push($data, $simulations, $model_id, $month, $day, $hour, $minute, $tz_sign,$tz_hour,$tz_minute,$weather, $status, $ctime);

    return $data;
  }

  // Will generate the html elements that load in this prevoius task list
  function prevTaskList()
  {
    // Collect all the data on the task
    $data = $this->get_user_simulations();
    $generated_html =  "<table class='table table-condensed' style='table-layout:fixed; width:100%;'>";
    $generated_html = $generated_html."
    <thead><tr>
      <th class='col-md-4'>Title</th>
      <th class='col-md-1'>Id</th>
      <th class='col-md-1'>Edit #</th>
      <th class='col-md-1'>Date</th>
      <th class='col-md-1'>Time</th>
      <th class='col-md-1'></th>
      <th class='col-md-1'>Weather</th>
      <th class='col-md-2'></th>
    </tr></thead>
    <tbody>";

    // For each task we have
    for( $i=count($data[0]) - 1; $i >= 0 ; $i--)
    { 

      $path            = $data[0][$i];
      $id              = $data[1][$i];
      $month           = $data[2][$i];
      $day             = $data[3][$i];
      $hour            = $data[4][$i];
      $minute          = $data[5][$i];
      $tz_sign         = $data[6][$i];
      $tz_hour         = $data[7][$i];
      $tz_minute       = $data[8][$i];
      $weather         = $data[9][$i];
      $status          = $data[10][$i];
      $ctime           = $data[11][$i];
      
      $entry = $this->task_entry($id,$month,$day,$hour,$minute,$tz_sign,$tz_hour,$tz_minute,$weather,$status,$path)."\n";

      $generated_html = $generated_html."<tr>".$entry."</tr>";

    }

    if( count($data[0]) == 0 ){

      $generated_html = "<center><p> Looks like you haven't run a simulation yet. Create a <b>new task</b> to run a simulation! </p></center>";
    }

    $generated_html = $generated_html."</tbody></table>";
    $log_handler = fopen("../user_output/form_log.txt", "w");
    fwrite($log_handler, $generated_html);

    return $generated_html;
  }

  function currentModelInfo($path)
  {
    $pieces = explode("/",$path);
    $pieces = explode("_",$pieces[4]);

    //0 is sim, 1 is date, 2 is time, 3 is weather
    $date = $pieces[1];
    $time = $pieces[2];
    $timezone = $pieces[3];
    $weather = $pieces[4];

    $mon    = substr($date,0,2);
    $dy     = substr($date,2,2);
    $hr    = substr($time,0,2);
    $mi    = substr($time,2,2);
    
    if(strlen($timezone) == 6){
      $tzs     = substr($timezone,0,1);
      $tzhr    = substr($timezone,2,2);
      $tzmi    = substr($timezone,4,2);
    }
    else if(strlen($timezone) == 5){
      $tzs     = "+";
      $tzhr    = substr($timezone,1,2);
      $tzmi    = substr($timezone,3,2);
    }

    $output = "<b>Simulation Info</b><br>";
    $output = $output."Date: ".$mon."/".$dy."<br>";
    $output = $output."Time: ".$hr.":".$mi."<br>";
    $output = $output."Timezone: GMT ".$tzs.$tzhr.":".$tzmi."<br>";
    $output = $output."Weather: ".$weather."<br>";
    return $output;
  }

  function task_entry($id,$month,$day,$hour,$minute,$tz_sign,$tz_hour,$tz_minute,$weather,$status,$path)
  {
    // given the id, month,day,hour,minute, and weather
    $generated_html = "";
    // get the title
    require_once('config.inc.php'); // Connect to DB

    // Query the database for models made by users
    $cmd = "SELECT username FROM model_meta WHERE id='$id';";
    error_log("user.php:task_entry: ".$cmd);
    $res = pg_query($cmd) or die('Failed to get previous models');

    // Getting title and renovation numbers
    $row   = pg_fetch_row($res); 
    $username = $row[0];

    // We want to check if this is the most recent renovation
    $cmd = "SELECT user_model_num,user_renov_num FROM model_meta WHERE id='$id';"; 
    $res = pg_query($cmd) or die('Failed to get previous models'); // $res = [0,0]

    $row = pg_fetch_row($res); 
    $user_model_num = $row[0];
    $user_renov_num = $row[1];

    // Get all models that have this user_model_num
    $cmd = "SELECT id,title FROM model_meta WHERE username=$1 AND user_model_num=$2 ORDER BY user_renov_num DESC"; // %
    $res = pg_query_params($cmd, array($username, $user_model_num)) or die('Failed to get previous models');
    $row   = pg_fetch_row($res); 

    $latest = $row[0];
    $title = $row[1];

    // The line below is just for debugging
    // $generated_html = $generated_html."<td>$title (id:".$id." latest:".$latest." user_model_num:".$user_model_num." )</td>";
    
    // The line below is just for debugging
    // $generated_html = $generated_html."<td>$title (id:".$id." latest:".$latest." )</td>";
    if($id == $latest){
      // This is the latest model
      // $generated_html = $generated_html."<td>$title</td>";
      $generated_html = $generated_html."<td>$title</td> <td>$id</td> <td>0</td>";
    }else{
      // This is not the latest model
      $generated_html = $generated_html."<td>$title</td> <td>$id</td> <td>$user_renov_num</td>";
    }




    // Insert Date
    $m              = str_pad( $month , 2, "0", STR_PAD_LEFT);
    $d              = str_pad( $day   , 2, "0", STR_PAD_LEFT);
    $date_str       = $m.'/'.$d;
    $generated_html = $generated_html."<td>$date_str</td>";

    // Insert Time
    $hr             = str_pad( $hour   , 2, "0", STR_PAD_LEFT);
    $mn             = str_pad( $minute , 2, "0", STR_PAD_LEFT);
    $time_str       = $hr.":".$mn;
    $generated_html = $generated_html."<td>$time_str</td>";

    // Insert Timezone
    if($tz_sign > 0)
      $tzs = "+";
    else
      $tzs = "-";

    $tzhr             = str_pad( $tz_hour   , 2, "0", STR_PAD_LEFT);
    $tzmn             = str_pad( $tz_minute , 2, "0", STR_PAD_LEFT);
    $tz_str           = "GMT ".$tzs.$tzhr.":".$tzmn;
    $generated_html = $generated_html."<td class='timezone'>$tz_str</td>";

    // Insert Weather

    $generated_html = $generated_html."<td>$weather</td>";

    // =============================================================================
    // Creation of ERROR and VIEW button
    // =============================================================================
    
    if($status == 'success')
    {
      $generated_html = $generated_html."<td class='buttonalign'><button onclick=\"load_res('$id', '$path'); return false;\" class='buttonsize'>view</button></td>";
    }

    if($status == 'pending')
    {
      // $generated_html = $generated_html."<button onclick=\"load_res($id, '$path'); return false;\" class='inputs' disabled>&nbsp &nbsp pending &nbsp &nbsp</button>\n";
      $generated_html = $generated_html."<td class='buttonalign'><img src=\"../images/ajax-loader.gif\"> In Queue<td>";
    }

    if($status == 'running')
    {
      // $generated_html = $generated_html."<button onclick=\"load_res($id, '$path'); return false;\" class='inputs' disabled>&nbsp &nbsp pending &nbsp &nbsp</button>\n";
      $generated_html = $generated_html."<td class='buttonalign'><img src=\"../images/ajax-loader.gif\"> Running<td>";
    }

    if($status == 'error')
    {
      $generated_html = $generated_html."<td class='buttonalign'><button onclick=\"load_error('$id', '$path'); return false;\" class='buttonsize'>error</button></td>";
    }

    // call the fuction load_res(13, '../var/www/')
    
    // $generated_html = $generated_html."<td><button onclick=\"delete_model($id, '$path'); return false;\" class='inputs'>&nbsp &nbsp X &nbsp &nbsp</button></td>";

    return $generated_html;
  }

} //user obj

?>
