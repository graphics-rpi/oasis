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
    "task_tab.php",                   // script
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

    <link rel="stylesheet" href="../css/extrastuff.css">

    <!--   jquery includes  -->
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/jquery.form.min.js"></script>
    <!-- \ jquery includes  -->

    <!--   generate_HTML_form -->
    <script src="../js/feedback_util.js"></script>
    <!-- \ generate_HTML_form -->

    <!-- libraries used for the sketching interface -->
    <script src="../js/lib/raphael.js"></script>
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/lib/spin.min.js"></script>
    <script src="../js/objFileContents.js"></script>
    <script src="../js/util.js"></script>

    <!--Bootstrap and related libraries -->
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/lib/bootbox.min.js"></script>
    <!--Bootstrap and related libraries -->

    <!--Loading virtual tabletop and models-->
    <!-- <script src="../js/sketching_ui.js"></script> -->
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
    <script src="../js/sketching_ui.js"></script>

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


    <!-- Create the ribbon -->
    <script> 
        var  GLOBAL_RIBBON_STATE   ="not loaded";
        var  GLOBAL_RIBBON_TAB     = "tab-task";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-task");
        GLOBAL_RIBBON_STATE = "loaded"; // helps the tab_hanlder
        window.ribbon1.disableTabs(["tab-nav-next"]);
    </script>

    <!-- Every 3 seconds triggers a script to reload the task table -->
    <script>
      $(document).ready(function(){
        setInterval(function() {
            $("#task_list").load("../php/reload_task_page.php");
        }, 10000);
      });
    </script>

    <script type="text/javascript">

        function hidehelp() {
            document.getElementById("sidecontent_task").style.width = 0;
            document.getElementById("sidecontent_task").style.display = "none";
            document.getElementById("maincontent_task").style.width = "100%";
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
                        $('#bug_report')[0].reset();
                    }
                });
            return false;
            });
        });
    </script>

    <div id="tutorial_video" class="video">
    <iframe id="iframe" style="height:90%; width:100%; boarder:none" src=""></iframe>
    <center>
        <br>
        <button type="button" class="btn btn-danger" onclick="hide_video()">Close</button>
    </center>
    </div>

    <div id= "overlay" class="overlay"> </div>

      <div id="container-parent" class="task_tab_parent">
          
        <div id="maincontent_task">
          <div id="new_task_list">
          </div>
          <div id="task_list">
            <?php
              // This php script allows the auto generations of prevous models
              // As well as calls to the load_previous_model function when they are pressed;
              require_once('../php/user.php');
              require_once('../php/model.php');

              // Getting user object
              $userobj = unserialize($_SESSION['user']);
              $generated_html = $userobj->prevTaskList();
            ?>
            <?php
              echo $generated_html;
            ?>
          </div>
        </div>
        <div id="sidecontent_task">
          <div id="help" class="help_tab_task">
            <div id="helplinks">
              <h2>Help</h2>
              <a href="#" onclick="helpcontents('new')" >How do I create a new task?</a>
              <br>
              <a href="#" onclick="helpcontents('change')">What parameters can I change?</a>
              <br>
              <a href="#" onclick="helpcontents('task')">What happens after I submit a task?</a>
              <br>
              <a href="#" onclick="helpcontents('error')">What does an <b>error</b> button mean?</a>
            </div>
            <hr>
            <div id="helpcontents">
                <div id="new" class="helpcontent">Click on the <b>New Task</b> button in the upper left. </div>

                <div id="change" class="helpcontent">Specify the date and time, which control the relative position of the sun.
                 You may also change the timezone of your model. Finally, select the weather, which changes the quantity of direct sun vs. skylight. </div>
                <div id="task" class="helpcontent">After you submit a task, a spinner will appear letting you know that your simulation is running.  After a brief period of time a <b>view</b> button will replace the spinner. Click on the <b>view</b> button to see the simulation results.                </div>


              <div id="error" class="helpcontent">Oops! Something likely went wrong on our end. Try remaking the task again. If you are stuck please file a bug report by clicking on the <b>bug report</b> button above.</div>
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
                <input type="submit" value="submit" class="buttonsize"></input>
            </form>
            <div id="successmsg">Thank you for submitting a bug report!</div>
          </div>

        </div>
        <div id='error_pane'></div>
      </div>
      <div id="status_pane"></div>
  </body>

<script>
//load_non_submitted_task(); 
$(document).ready(function(){ 
  // setTimeout( function() { sketching_ribbon_handler('button-daylight-refresh'); } , 5000);
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
    
</script>
