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
    "3d_tab.php",                   // script
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

     <script src="../js/viewer.js"></script>
    <!--Bootstrap and related libraries -->

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
        var  GLOBAL_RIBBON_TAB     = "tab-3d";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-3d");
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

        <div id="container"></div>
       
      </div><!--maincontent-->
        <div id="sidecontent">
          <div id= "title">
          </div>
          <!-- php script will display model information only if we are viewing renderings -->
          <?php  if(False){  ?>
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

          <div id="feedback" class="feedback" >

            <div id="form_3d" >
              <form id="fb_3d_form" action="../php/3d_remesh_submit_feedback.php" method="post">

                <p>Does the 3D model match your design intentions?<br>
                <input type="radio" name="correct" value="correct"> Matched my intentions (no revisions required)<br>
                <input type="radio" name="correct" value="revisons"> Matched my intentions (revisions were required)<br>
                <input type="radio" name="correct" value="failed"> Failed to match my intentions (even after revision)<br></p>

                <hr>

                <p>Describe your overall impression of the system's effectiveness in constructing a 3D model from your design:<br>
                <textarea name="impression" rows="3" cols="50" > </textarea></p>
                
                <p>Describe cases where the system incorrectly interpreted your design intentions: <br>
                <textarea name="failures" rows="3" cols="50" > </textarea></p>

              </form>
            </div> <!--form_3d-->

          </div><!--feedback-->

          <div id="help" class="help_tab">
            <div id="helplinks">
              <h2>Help</h2>
              <a href="#" onclick="helpcontents('model')" >How do I look around my model?</a>
              <br>
              <a href="#" onclick="helpcontents('ceiling')">What is <b>Toggle Ceiling</b>?</a>
              <br>
              <a href="#" onclick="helpcontents('after')">What do I do after veiwing my model?</a>
            </div>
            <hr>
            <div id="helpcontents">
                <div id="model" class="helpcontent">Explore your model using your mouse and keyboard. Left mouse button to rotate the model, right mouse (or ctrl + left mouse) button to zoom in/out, and the directional arrows to move up, down, left or right.</div>
                <div id="ceiling" class="helpcontent">The <b>Toggle Ceiling</b> button renders the ceiling to help visualize your room.</div>
                <div id="after" class="helpcontent">After you are done viewing your model, click on <b>Step 4: Create Daylighting Simulation </b> tab above  to render lighting in your
                  created space.</div>
                
            </div>
          </div>

          <div id="bug">
            <h2>Bug Report</h2>
            <form id= "bug_report" action="../php/submit_bug_report.php" method="post" onsubmit="">
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
                <div class="button-bug"><input type="submit" class='buttonsize'> </div>
            </form>
          </div>
          
        <script>
            var path = getScrambledPath();  // just going to get view path
            viewer = $('#container').viewer(path,false);
        </script>
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
    save_form('fb_3d_form');
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
      save_form('fb_3d_form');
      //ready_ajax("Feedback Saved")
      TRIGGER = false;
    }
  }

  $("#fb_3d_form").change(function(){
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
    '../php/3d_remesh_get_feedback.php',
    { },
    function(e){
      var form = document.getElementById("fb_3d_form");

      if(e['correct'] != "" ){
        form.correct.value = e['correct'];
      }

      if( e['failures'] != "" ){
        form.failures.value = e['failures'];
      }

      if( e['impression'] != "" ){
        form.impression.value = e['impression'];
      }
      window.ribbon1.enableRibbon(); // to prevent quickly switching tabs &  losing feedback

  });

});
        

</script>
