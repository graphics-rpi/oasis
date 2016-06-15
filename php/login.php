<?php
session_start();
require_once("user.php");
require_once('model.php');

// The email and password are sent through the post method
// We can retrive anything sent via post as so
$email    = $_POST["email"];
$password = $_POST["password"];

// Creating a new user object so we can query loginDB
$created_user = new User($email,$password);

if ($created_user->authenticate()) 
{

  pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
    date("Y-m-d").'|'.date("h:i:sa"),
    $email,
    "",
    "login.php authenticated",
    ""
  ));

  // This is the model we are looking to load
  $id                  = "Not Assigned";

  // Meta
  $title               = "Not Assigned";
  $user_model_num      = "Not Assigned";
  $user_renov_num      = "Not Assigned";

  // Data
  $wallfile_txt        = "Not Assigned";
  $paths_txt           = "Not Assigned";

  // =========================================================
  // TODO: Find values to assign before uploading working model
  // =========================================================

  // Lets collect all the models which have to do with this user
  // $cmd = "SELECT * FROM model_meta WHERE username=$1 ORDER BY user_model_num DESC, user_renov_num DESC;";
  $cmd = "SELECT * FROM model_meta WHERE username=$1 ORDER BY id DESC;";
  $res = pg_query_params($cmd, array($email));


  if( pg_num_rows($res) > 0 ) // if we have previously made models
  { 

    // Load lastly created model
    $data = pg_fetch_row($res);
    $id = $data[0];
    $title = $data[1];
    $user_model_num = $data[3];
    $user_renov_num = $data[4];

    // Lets collect the meta data from this model
    $cmd = "SELECT * FROM model_data WHERE id=$1";
    $res = pg_query_params($cmd, array($id));

    if(pg_num_rows($res) > 1)
    { 
      error_log("Returned more then one model with id"); 
      die("Returned more then one model with id");
    }

    // Load all the metadata
    $data = pg_fetch_row($res);
    $wallfile_txt = $data[1];
    $paths_txt    = $data[2];

    // Update the working/session model
    $session_model = new Model(
      $id,
      $title,
      $user_model_num,
      $user_renov_num,
      $wallfile_txt,
      $paths_txt
    );

    // Setting this as exisiting status
    $session_model->setStatus('Exisiting');

    // Change view path
    $user_folder_name  = $created_user->getUserFolderName();
    $_SESSION['view_path'] = '../user_output/geometry/'.$id.'/slow/';

    $created_user->workingModel = $session_model;
    $_SESSION['user'] = serialize($created_user);


  }
  else // if we have no models made so far
  {
    // Update the working/session model
    $session_model = new Model($id, $title, $user_model_num, $user_renov_num, $wallfile_txt, $paths_txt);
    $session_model->setStatus("New");
    $created_user->workingModel = $session_model;
    $_SESSION['user']           = serialize($created_user);
    $_SESSION['task_container'] = "";

  }

  // Where task as saved, required for task tab
  $_SESSION['task_container'] = ""; 

  // Send users to load_lab upon sign in
  header("location:/pages/load_tab.php");
  exit;

}
else 
{
  // Failed to authenticate sending back to login page again
  header("location:/pages/login_page.php");
  exit;
}
?>




