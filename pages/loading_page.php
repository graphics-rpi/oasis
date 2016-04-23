<?php session_start(); 
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.
?>
<html>

  <head>

    <title>OASIS</title>

    <!-- Style sheet b/c we have so much style -->
    <link rel="stylesheet" href="../css/lib/pure.css">
    <link rel="stylesheet" href="../css/loading_page.css">

    <!-- Loading utils -->
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/raphael.js"></script>
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/lib/spin.min.js"></script>
    <script src="../js/objFileContents.js"></script>
    <script src="../js/util.js"></script>
    
  </head>

  <body>

    <div id="spinner"> </div>
    <div id="curtain"> </div>
    <script> generate_spinner() </script>

    <div id="box">

      <div id="title"> Your Models  </div>

      <div id="listbox">
          <div class="pure-menu pure-menu-open">

          <?php
          // This php script allows the auto generations of prevous models
          // As well as calls to the load_previous_model function when they are pressed;
          require_once('../php/user.php');
          require_once('../php/model.php');

          // Getting user object
          $userobj = unserialize($_SESSION['user']);
          $generated_html = $userobj->prevModelList();

          ?>
          
          <?php echo $generated_html ?>
          </div><!--pure box -->

      </div><!--listbox-->

        <div id="button">
        <a id="new_button" class="pure-button pure-button-primary" onclick="loadBlankModel()"> Create New Model </a>
        </div>

    </div><!--box-->


  </body>

</html>
