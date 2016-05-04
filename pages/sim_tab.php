<?php session_start(); 

  // logging events
  if (session_id() == '') {
    session_start();
  }

  require_once('../php/config.inc.php');          
  require_once("../php/user.php");
  require_once('../php/model.php');

  // Get user object and username and session model
  $userobj       = unserialize($_SESSION['user']);
  $username      = $userobj->username;
  $session_model = $userobj->workingModel;
  $id            = $session_model->id;

  
  pg_query_params('INSERT INTO error_table VALUES($1,$2,$3,$4,$5)', array(
    date("Y-m-d").'|'.date("h:i:sa").'|'.date('U'), // YYMMDD|HHMMSS
    $username,                        // username
    $id,                              // id (model)
    "sim_tab.php",                   // script
    ""                                // args
  ));

?>

<head>
    <meta charset="utf-8" />
    <title> OASIS </title>

    <!--  the main stylesheet for our ui framework -->
    <link rel="stylesheet" href="../css/main-style.css" />
    <!-- \ the main stylesheet for our ui framework -->

    <!--   Stylesheet AcidJs -->
    <link rel="stylesheet" href="../css/AcidJs.Ribbon.CustomStylesTool.css" />
    <link rel="stylesheet" href="../css/AcidJs.Ribbon.MyCustomDropdown.css" />
    <!-- \ Stylesheet AcidJs -->

    <!--  Style sheets for sketching interface  -->
    <link rel="stylesheet" href="../css/lib/pure.css">
    <!-- \ Style sheets for sketching interface  -->

    <!--   jquery includes  -->
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/jquery.form.min.js"></script>
    <!-- \ jquery includes  -->

    <!--   generate_HTML_form -->
    <script src="../js/feedback_util.js"></script>
    <!-- \ generate_HTML_form -->

    <!-- libraries used for the rendering and webgl interface -->
    <script src="../js/lib/raphael.js"></script>
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/objFileContents.js"></script>
    <script src="../js/sketching_ui.js"></script>
    <script src="../js/util.js"></script>
    <script src="../js/lib/three.js"></script>
    <script src="../js/lib/THREEx.KeyboardState.js"></script> 

    <!--Bootstrap and related libraries -->
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/lib/bootbox.min.js"></script>
    <script src="../js/lib/clipboard.min.js"></script>
    
    <!--Bootstrap and related libraries -->
    <script src="../js/viewer.js"></script>
</head>

<body>

    <!-- Ribbon User Interface -->
    <div id="ribbon-div">
      <div id="ribbon-ui"> </div>
    </div>

    <!-- register control's runtime libraries -->
    <script src="./AcidJs.Ribbon/classes/Ribbon.js"></script>

    <!-- / register control's runtime libraries -->
    <!-- loads the event handlers in addition to ribbon -->
    <script src="../js/ribbon_events.js"></script>
    <!-- \loads the event handlers in addition to ribbon -->

    <!-- Where the ribbon is defined -->
    <?php

        if(!isset($_COOKIE['oasis_developer'])) {
          echo "<script src='../js/ribbon_ui.js'></script>";
        }else{
          echo "<script src='../js/ribbon_ui_dev.js'></script>";
        }

      ?>

    <script type="html/x-acidjs-ribbon-template" id="styles_custom_tool_template">
      <div class="my-custom-styles-tool" data-tool-name="my-custom-styles">
        <ul class="acidjs-ui-ribbon-tool-exclusive-buttons">
          <# for(var i=0 ; i < styles.length; i ++) { #>
            <# var style=s tyles[i]; #>
              <# var selected="acidjs-ui-tool-active" ; #>
                <li>
                  <a class="<#= i === 0 ? selected : " " #>" title="AcidJs.Ribbon supports custom tools via templating. This one is custom." class="<#= styles.cssClass #>" href="#" data-value="<#= style.value #>" name="<#= commandName #>">
                    <strong>AaBbCc</strong>
                    <span><#= style.name #></span>
                  </a>
                </li>
                <# } #>
        </ul>
      </div>
    </script>

    <script type="html/x-acidjs-ribbon-template" id="my_custom_dropdown_template">
      <h5><#= title #></h56>
        <p><#= text #></p>
        <ul class="my-custom-dropdown-tool">
            <# for(var i = 0; i < guitars.length; i ++) { #>
                <li>
                    <a href="#" data-value="<#= guitars[i] #>" name="guitar"><#= guitars[i] #></a>
                </li>
            <# } #>
        </ul>
    </script>

    <!-- make the help menu appear/disappear -->
    <script type="text/javascript">
        function hidehelp() {
            var itemDivs = document.getElementById("sidecontent").children;
            for(var i = 0; i < itemDivs.length; i++) {   
                itemDivs[i].style.display = 'block';   
            }
            document.getElementById("help").style.display = "none";
        }

        function helpcontents(s1) {
            var itemDivs = document.getElementById("helpcontents").children;
            for(var i = 0; i < itemDivs.length; i++) {   
                itemDivs[i].style.display = 'none';   
            }
            document.getElementById(s1).style.display = "block";
        }
    </script>

    <!-- bug report no reload -->
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('submit', '#bug_report', function() {
                var data = $(this).serialize();
                $.ajax({
                    type : 'POST',
                    url  : '../php/submit_bug_report.php',
                    data : data,
                    success :  function(data) {
                        document.getElementById("successmsg").style.display = 'block';
                    }
                });
            return false;
            });
        });
    </script>

    <!-- Create the ribbon -->
    <script> 
        window.ribbon1.disableRibbon(); // to prevent quickly switching tabs &  losing feedback
	      setTimeout(function(){window.ribbon1.enableRibbon();},1000);
        var  GLOBAL_RIBBON_STATE   = "not loaded";
        var  GLOBAL_RIBBON_TAB     = "tab-analysis";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-analysis");
        window.ribbon1.disableTabs(["tab-nav-next"]);
        window.ribbon1.enableTabs(["tab-analysis"]);
        GLOBAL_RIBBON_STATE = "loaded"; // helps the tab_hanlder
    </script>

    <div id="tutorial_video" class="video">
    <iframe id="iframe" style="height:90%; width:100%; boarder:none" src=""></iframe>
    <center>
        <br>
        <button type="button" class="btn btn-danger" onclick="hide_video()">Close</button>
    </center>
    </div>

    <div id= "overlay" class="overlay"> </div>

    <div id="container-parent" class="three_d_tab_parent">

      <div id="maincontent">

        <div id="container">
          
          <?php

            $view_path = $_SESSION['view_path'];
            $view_path = explode("/", $view_path);
            $view_path = explode("_",$view_path[4]);
            if($view_path[5] == "fcv"){

              echo "<script>";
              echo "activateTool(\"button-3d-fcv\");";
              echo "</script>";

              echo "<center>";
              echo "  <img src=\"../images/blu_check.png\"> Under illumination &nbsp <img src=\"../images/red_check.png\"> Over illumination";
              echo "</center>";
            }
          ?>

        </div>


<!--        <script src="../js/render_model.js"></script>-->
<!--        <script src="../js/copy.js"></script>-->
      </div><!--maincontent-->

        <div id="sidecontent">
          <div id= "title">
          </div>
          <!-- php script will display model information only if we are viewing renderings -->
          <?php  if(True){  ?>
              <div id="modelinfo">
                <?php
                  require_once('../php/user.php');
                  require_once('../php/model.php');
                  $userobj = unserialize($_SESSION['user']);
                  $generated_html = $userobj->currentModelInfo($_SESSION['view_path']);
                  print_r($generated_html);
                ?>
              </div> <!-- modelinfo -->
              <hr>
          <?php } ?>

            <div id="form_sim">
              <form id="fb_sim_form" action="../php/3d_lsvo_submit_feedback.php" method="post">

                <p>
                  <input type="checkbox" name="publish" />
                  Share this model with the OASIS Community
                </p>

              
                <p>Did you understand the results of the simulation? Describe anything confusing or unclear:<br>
                <textarea name="understand" rows="3" cols="50" > </textarea></p>

                <hr>
                
                <p>Did the system allow you to create and test daylighting performance? Do you understand the areas of over illumination and under illumination? <br>
                <textarea name="limitations" rows="3" cols="50" > </textarea></p>


              </form>
            </div><!--form_sim-->

          <div id="help" class="help_tab">
            <div id="helplinks">
              <h2>Help</h2>
              <a href="#" onclick="helpcontents('model')" >How do I look around my model?</a>
              <br>
              <a href="#" onclick="helpcontents('analysis')">What is the <b>Analysis</b> button?</a>
              <br>
              <a href="#" onclick="helpcontents('after')">What do I do after viewing my model?</a>
            </div>
            <hr>
            <div id="helpcontents">
                <div id="model" class="helpcontent"> Explore your model using your mouse and keyboard. Left mouse button to rotate the model, right mouse (or ctrl + left mouse) button to zoom in/out, and the directional arrows to move up, down, left or right.  </div>
                <div id="analysis" class="helpcontent">The <b>Analysis</b> button shows under illuminated areas as blue-checkerboard and over illuminated areas as red-checkerboard.</div>
                <div id="after" class="helpcontent">
                  After you are done viewing this simulation, you can go back to <b>Step 4: Create Daylighting Simulation</b> to render the same model at a different time of day.<br><br>
                  Click on <b>Step 2: Sketch a Room</b> to make edits to your previous model. <br><br>
                  Click on <b>Step 1: Create/Load Model</b> to create another model.<br> <br>
  
                </div>
            </div>
          </div>

           <div id="bug">
            <h2>Bug Report</h2>
            <form id= "bug_report" method="post">
                <p> [Optional] Please enter your email so that we may follow up on your report:<br>
                <input type="email" name="bug_email" placeholder="joe@doe.com"></p>
                <p>Describe your intentions:<br>
                <textarea name="intentions" rows="2" cols="50"></textarea></p>
                <p>Describe the actual outcome:<br>
                <textarea name="outcome" rows="2" cols="50"></textarea></p>
                <p>List the steps reproduce your problem/bug:<br>
                <textarea name="repro" rows="2" cols="50"></textarea></p>
                <p>Enter any other relevant information not described above:<br>
                <textarea name="other" rows="2" cols="50"></textarea></p>
                <div class="bugbutton"><input type="submit" value="submit" class="buttonsize"></div>
            </form>
            <div id="successmsg">Thank you for submitting a bug report!</div>
          </div>
            
        <script>
            var path = getScrambledPath();
//            console.log(path);
            viewer = $('#container').viewer(path,false);
        </script>
<!--        <script src="../js/render_model.js"></script>-->
        <script src="../js/copy.js"></script>
            

        </div><!--sidecontent-->
    </div><!--conterinerparent-->

    <div id="status_pane"> Ready </div>

</body>

<script>

$(window).bind('resize', function(e)
{
  if (window.RT) clearTimeout(window.RT);
  window.RT = setTimeout(function()
  {

    this.location.reload(false); /* false to get page from cache */


  }, 100);
});
    
  // Killing enter key on feedback form
  // Required on all tab templates
  $("feedback_class").bind("keypress", function (e) {
      if (e.keyCode == 13) {
          $("#btnSearch").attr('value');
          //add more buttons here
          return false;
      }
  });

    function set_model_title(){
    // Set the title field
    var t = "";
    $.ajax({ type: "POST", url: "../php/get_title_from_view_path.php", async: false, 
    success : function(e) { 
        var json = JSON.parse(e);
        t += json.data; 
        document.getElementById("title").innerHTML="<p><b>Model Name</b><br>" + t + "</p>";
        }
    });
    }


  function show(id)
  {
    $("#"+id).show();
  }

  function hide(id)
  {
    $("#"+id).hide();
  }
  
  
// On Load Scripts ( Same for all pages )    
$(document).ready(function() { 
    
  // Setting title field of model
  set_model_title();
  
  
  // Auto save functions
  function auto_save()
  {
  
      if( TRIGGER ){
        save_form('fb_sim_form');
        // ready_ajax("Feedback Saved")
        TRIGGER = false;
      }
  }
  
  $("#fb_sim_form").change(function(){
      busy_ajax("Saving Feedback");
      TRIGGER = true;
      setTimeout(auto_save, 100);
  });
  
  $(":input").on('input', function(){
      busy_ajax("Saving Feedback");
      TRIGGER = true;
      setTimeout(auto_save, 5000);
  });
  
  
  $.getJSON(
    '../php/3d_lsvo_get_feedback.php',
    { },
    function(e){
  
      var form = document.getElementById("fb_sim_form");

      if(e['publish'] == "true"){
          form.publish.checked = true;
      }else{
          form.publish.checked = false;
      }
  
      if( e['understand'] != "" ){
        form.understand.value = e['understand'];
      }
  
      if( e['limitations'] != "" ){
        form.limitations.value = e['limitations'];
      }
  
      window.ribbon1.enableRibbon(); // to prevent quickly switching tabs &  losing feedback
  });
    
});
        
</script>
