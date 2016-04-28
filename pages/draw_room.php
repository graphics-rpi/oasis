<?php session_start(); ?>
<head>

  <title>OASIS </title>

  <!-- Style sheet b/c we have so much style -->
  <link rel="stylesheet" href="../css/lib/pure.css">
  <link rel="stylesheet" href="../css/draw_room.css">

  <!-- Loading Bunchies or Libraries!-->
  <script src="../js/lib/jquery.js"></script>
  <script src="../js/lib/raphael.js"></script>
  <script src="../js/lib/raphael.free_transform.js"></script>
  <script src="../js/lib/spin.min.js"></script>
  <script src="../js/objFileContents.js"></script>
  <script src="../js/util.js"></script>

  <script src="../js/lib/three.js"></script>
  <script src="../js/lib/THREEx.KeyboardState.js"></script> 

  <!--Loading virtual tabletop and models-->
  <script src="../js/sketching_ui.js"></script>

</head>

<body> 
  
  <script> 
    // 1) make session longer // 2) prompt user to keep session
    setInterval(function(){ window.location = "../index.html";},  24*60*1000); 
  </script>

  <!-- Loading A 3D Wall Model and Container -->
  <div id="canvas"></div>
<!--  <script src="../js/render_model.js"></script>-->


  <!-- Loading -->
  <div id="spinner"> </div>
  <div id="curtain"> </div>
  <script> generate_spinner() </script>

  <!--Bad Model DIV-->
  <div id="model_failed"> 
  <center><h2> You Broke it!</h2></center>

  <p>
    No you didn't really, the model created was probably to 
    complex/ambiguous for our algorithms
    to run daylighting simulations on. If you are fond
    of this model you can always try a 
    few of the options below and try to render again.
  </p>

  <ul>
    <li>Try to reduce the number of intersecting walls</li>
    <li>Try to reduce the number of different height walls that intersect each other</li>
    <li>Try to reduce the number of windows and prevent windows from self intersecting</li>
    <li>Try to make the model bigger with less overlapping walls </li>
  </ul>
  
  <p>If this doesn't work then we record the models that failed to create an attempt to improve the algorithms behind it!</p>
  <a class="pure-button pure-button-primary" id="info_button" onclick="close_failed_box()">Close</a> 
  </div>


  <!-- Broken LSVO -->
  <div id="lsvo_failed"> 
  <center><h2>Where did the sun go?</h2></center>
  <p>
    Lighting is calculated by finding interior and exterior spaces, and through photon mapping.
    However if the model is too ambiguous, hard to guess what is inside and outside, daylighting simulation could fail.
    <br>
    A few fixes:
  </p>
  
  <ul>
    <li>Include at least 1 window into the model</li>
    <li>Try to make the model less ambiguous</li>
    <li>Try to reduce the number of intersecting walls</li>
    
  </ul>

    <a class="pure-button pure-button-primary" id="info_button" onclick="close_lsvo_failed_box()">Close</a> 
  </div>

  <!--INFO BOX DIV-->
  <div id="info"> 
      <center>
      <img src = "../images/info_wall.png"   ><br>Drag walls into the circle in the middle in order to design rooms<br>
      <img src = "../images/info_sam.png"    ><br>Visualization of the height of each wall selected<br>
      <img src = "../images/info_win.png"    ><br>Drag windows on to walls at least one is necessary for daylighting information<br> 
      <img src = "../images/info_compass.png"><br>Drag compass to change the orientation of the room<br> 
      <img src = "../images/info_new.png"    ><br>Create new model, will erase current model<br> 
      <img src = "../images/info_build.png"  ><br>Run Daylighting Simulation on generated model + render 3D view<br> 
      <img src = "../images/info_help.png"   ><br>See this information box again<br> <br>
      <a class="pure-button pure-button-primary" id="info_button" onclick="close_info()">Close</a> 
      </center>
  </div>

  <!--COMMENT DIV-->
  <div id="commentbox">
    <form>

        <h1>Comments</h1>

        <br> <h3>Give a title to your room design</h3>
        <input id="title" placeholder="RPI dorm room"><br><br>

        <br> <h3>Tell us about your room design</h3>
        <textarea rows="5" cols="50" id="comment_box">  </textarea> <br> <br>

        <h3>Comment about webpage</h3>
        <textarea rows="3" cols="50" id="concern_box">  </textarea> <br> <br>

        <a class="pure-button pure-button-primary" id="comment_button" onclick='run_render();'>Render</a>
        <a class="pure-button pure-button-primary" id="cancel_button"  onclick='close_commentbox();'>Back</a>
        
        <script> disable_enter("title"); disable_enter("comment_box"); disable_enter("concern_box");</script>
        
    </form>
  </div>

  <!--DATE/TIME DIV-->
  <div id="cal_input"> 
      <form class="pure-form">
      <fieldset>
        <legend>Month and Time</legend>
        <label for="month">Month</label>

        <select id="month">
          <option value="1" selected>JAN</option>
          <option value="2">FEB</option>
          <option value="3">MAR</option>
          <option value="4">APR</option>
          <option value="5">MAY</option>
          <option value="6">JUN</option>
          <option value="7">JUL</option>
          <option value="8">AUG</option>
          <option value="9">SEP</option>
          <option value="10">OCT</option>
          <option value="11">NOV</option>
          <option value="12">DEC</option>
        </select>
        <label for="time">Time</label>
        <select id="time">
          <option value="0"> 24:00 (midnight) </option>
          <option value="1"> 1:00</option>
          <option value="2"> 2:00</option>
          <option value="3"> 3:00</option>
          <option value="4"> 4:00</option>
          <option value="5"> 5:00</option>
          <option value="6"> 6:00</option>
          <option value="7"> 7:00</option>
          <option value="8"> 8:00</option>
          <option value="9"> 9:00</option>
          <option value="10">10:00</option>
          <option value="11">11:00</option>
          <option value="12" selected>12:00 (noon)</option>
          <option value="13">13:00</option>
          <option value="14">14:00</option>
          <option value="15">15:00</option>
          <option value="16">16:00</option>
          <option value="17">17:00</option>
          <option value="18">18:00</option>
          <option value="19">19:00</option>
          <option value="20">20:00</option>
          <option value="21">21:00</option>
          <option value="22">22:00</option>
          <option value="23">23:00</option>
        </select>
      </fieldset>
      </form> 

  </div>
    <ul id="nav">
      <li>&nbsp&nbsp</li>
      <li><button class="pure-button" onclick='logout_pressed()'> Sign out </button></li>
      <li>&nbsp&nbsp</li>
      <li><button class="pure-button" onclick='loading_page_pressed()'> Load Previous Model </button></li>
      <li>&nbsp&nbsp</li>
      <?php require_once('../php/user.php'); require_once('../php/model.php');$user=unserialize($_SESSION['user']);
      echo "<li><h4>$user->username</h4></li>"?>
      <li>&nbsp&nbsp</li>
    </ul>

</body>

</html>
 
