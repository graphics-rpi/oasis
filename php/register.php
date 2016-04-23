<?php
   session_start();
   require_once("user.php");
   require_once('model.php');

   error_log("register.php start");

   // The email and password are sent through the post method
   // We can retrive anything sent via post as so
   $email     = $_POST["email"];
   $password1 = $_POST["password1"];
   $password2 = $_POST["password2"];
   $include = isset($_POST['include']);

   error_log("include user in study: ".$include);

   // Password Check
   if($password1 != $password2){
     $_SESSION['error'] = "Password does not match";
     header("location:../pages/register_page.php");
     exit;
   }

   // Create user
   $created_user = new User($email,$password1);

  // Check for repeat users
  if ($created_user->exisiting_user()) {
    // Redirect to register again already in DB
    $_SESSION['error'] = "Email already in use";
    header("location:../pages/register_page.php");
    exit;
  }

  // Register him/her
  error_log("register.php: before register_user()");
  $created_user->register_user($include);
  error_log("register.php: after register_user()");
   
  // CONFIRMATION and REDIRECT
  if ($created_user->authenticate()) {

    // Setting up users folder
    $created_user->createUserFolder();
    
    // Creating default/blank fields in session model
    $id = "Not Assigned";

    // Meta
    $title          = "Not Assigned";
    $user_model_num = "Not Assigned";
    $user_renov_num = "Not Assigned";

    // Data
    $wallfile_txt = "Not Assigned";
    $paths_txt    = "Not Assigned";

    // Update the working/session model
    $session_model = new Model($id, $title, $user_model_num, $user_renov_num, $wallfile_txt, $paths_txt);
    $session_model->setStatus("New");
    $created_user->workingModel  = $session_model;
    $_SESSION['user']            = serialize($created_user);
    $_SESSION['task_container']  = "";

    error_log("register.php: Authenticated ");
    header("location:../pages/load_tab.php");
    exit;
  
  // Didn't register correctly
  }else{
    error_log("register.php: Not Authenticated");
    header("location:../pages/register_page.php");
    $_SESSION['error'] = "Registration Error";
    exit;
  }


error_log("register.php end");

  
?>


