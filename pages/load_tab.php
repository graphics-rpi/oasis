<?php 

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
    "load_tab.php",                   // script
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
    <!-- \ jquery jk  -->

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
    </script><!--styles_custom_tool_template-->

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
    </script><!--my_custom_dropdown_template-->

    <!--Initalize Ribbon -->
    <script> 
        window.ribbon1.disableRibbon(); // to prevent quickly switching tabs &  losing feedback
	setTimeout(function(){window.ribbon1.enableRibbon();},1000);
        var  GLOBAL_RIBBON_STATE   ="not loaded";
        var  GLOBAL_RIBBON_TAB     = "tab-load";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-load");
        GLOBAL_RIBBON_STATE = "loaded"; // helps the tab_hanlder

        window.ribbon1.disableTabs(["tab-nav-back"]);

        //console.log("SET GLOBAL_RIBBON_STATE == loaded");
    </script>

    <script type="text/javascript">
        function hidehelp() {
            var itemDivs = document.getElementById("sidecontent").children;
            for(var i = 0; i < itemDivs.length; i++) {   
                itemDivs[i].style.display = 'none';   
            }
            document.getElementById("fb_load").style.display = "block";
        }

        function helpcontents(s1) {
            var itemDivs = document.getElementById("helpcontents").children;
            for(var i = 0; i < itemDivs.length; i++) {   
                itemDivs[i].style.display = 'none';   
            }
            document.getElementById(s1).style.display = "block";
        }

    </script>

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

    <style type="text/css">
    </style>




    <div id="tutorial_video" class="video">
    <iframe id="iframe" style="height:90%; width:100%; boarder:none" src=""></iframe>
    <center>
        <br>
        <button type="button" class="btn btn-danger" onclick="hide_video()">Close</button>
    </center>
    </div>

    <div id= "overlay" class="overlay"> </div>

    <div id="container-parent" class="load_tab_parent">
        
        <div id="sidecontent">
            <div id="feedback" class="feedback" > 

              <form id= "fb_load_form" action="../php/load_submit_feedback.php" method="post">

                <input type="checkbox" name="include" /><label for="include"></label>
                <b>I give permission for my designs and feedback to be used in future publications</b>
                <p></p>
                <p>Are you affiliated with Rensselaer Polytechnic Institute? <br>
                <input type="radio" name="rpi_affilate" value="true" onchange="show('rpi_affilate_div')"> Yes <br>
                <input type="radio" name="rpi_affilate" value="false" onchange="hide('rpi_affilate_div')"> No <br></p>

                <div id="rpi_affilate_div" style="display:none;">
                  <p>How are you affiliated with RPI? &nbsp
                  <select name="affiliation" id="affiliation">
                    <option value="">Select</option>
                    <option value="undergraduate">Undergraduate</option>
                    <option value="graduate">Graduate</option>
                    <option value="staff">Staff</option>
                    <option value="faculty">Faculty</option>
                  </select> </p>
                </div>

                <p>Years of formal education in architecture? <br>
                <input type="number" min="0" max="100" name="arch_edu"><br></p>

                <p>Years of formal education in visual arts? <br>
                <input type="number" min="0" max="100" name="visual_edu"><br></p>

                <p>Years of job experience in architecture? (including internships) <br>
                <input type="number" min="0" max="100" name="arch_exp"><br></p>

                <p>Years of job experience in visual arts? (including internships) <br>
                <input type="number" min="0" max="100" name="visual_exp"><br></p>

                <p>Select the modeling software you have used:
                <div style="height: 6em; width: 12em; overflow: auto; border: 1px solid gray;";>
                  <input type="checkbox" name="software_list[]" id= "sketchup" value="sketchup"/> SketchUp <br>
                  <input type="checkbox" name="software_list[]" id="audocad" value="audocad"/> AutoCAD <br>
                  <input type="checkbox" name="software_list[]" id="rhino" value="rhino"/> Rhino <br>
                  <input type="checkbox" name="software_list[]" id="maya" value="maya"/> Maya <br>
                  <input type="checkbox" name="software_list[]" id="3dsmax" value="3dsmax"/> 3DS Max <br>
                  <input type="checkbox" name="software_list[]" id="cinema4d" value="cinema4d"/> Cinema 4D <br>
                  <input type="checkbox" name="software_list[]" id="blender" value="blender"/> Blender <br>
                  <input type="checkbox" name="software_list[]" id="revit" value="revit"/> Revit <br>
                </div>
                </p>

                <p>List additional modeling software you have used:<br>
                <input type="text" name="other_software"> </p>
                
                <p>Years of experience with modeling software?<br>
                <input type="number" min="0" max="100" name="software_exp"><br></p>

                <p>Other relevant education or experience? <br>
                <input type="text" name="other_exp"> </p>

                <p>[Optional] Are you colorblind? <br>
                <input type="radio" name="color_blind" value="true" > Yes <br>
                <input type="radio" name="color_blind" value="false"> No <br></p>

                <p>If we may follow up with questions about models created in our system, please enter your email. (We will not add you to a mailing list or share your email)<br>
                <input type="email" name="user_email" placeholder="joe@doe.com"> </p>
                <!--Testing removeal of submit button-->
                <!--<input type="submit" value="Submit">-->

              </form>
            </div>
            <div id="help" class="help_tab">
                <div id="helplinks">
                    <h2>Help</h2>
                    <a href="#" onclick="helpcontents('newmodel')" >How to create a new model</a>
                    <br>
                    <a href="#" onclick="helpcontents('prevmodel')">How to view a previous model</a>
                </div>
                <hr>
                <div id="helpcontents">
                    <div id="newmodel" class="helpcontent">Create a new model by clicking on <img src="../pages/AcidJs.Ribbon/icons/large/newproject.png"> in the upper left of the page. </div>
                    <div id="prevmodel" class="helpcontent">View a previously made model by clicking on one of
                        the names to the left. 
Create a new model by clicking on <img src="../pages/AcidJs.Ribbon/icons/large/newproject.png"> in the upper left of the page. 
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
                    <div class="bugbutton"><input type="submit" value="submit" class="buttonsize"></input></div>
                </form>
                <div id="successmsg">Thank you for submitting a bug report!</div>
            </div>
        </div> <!--sidecontent-->
        <div id="maincontent">
            <div id="modelsdump" >
                <?php
                // This php script allows the auto generations of prevous models
                // As well as calls to the load_previous_model function when they are pressed;
                require_once('../php/user.php');
                require_once('../php/model.php');

                // Getting user object
                $userobj = unserialize($_SESSION['user']);
                $generated_html = $userobj->prevModelList();

                ?>

                <div class="pure-menu pure-menu-open">
                <?php echo $generated_html ?>
                </div><!--pure-menu-->
            </div><!--modelsdump-->
        </div> <!--maincontent-->

    
    </div><!--container-prent-->
    <div id="status_pane"> <p> Ready </p> </div>


</body>

<script>


    var TRIGGER = false;

    // Auto save function
    function auto_save(){

        if( TRIGGER ){
          save_form('fb_load_form');
          //ready_ajax("Feedback Saved")
          TRIGGER = false;
        }
    }

    $("#fb_load_form").change(function(){
        busy_ajax("Saving Feedback");
        GLOBAL_FEEDBACK_STATUS = "SAVING";
        TRIGGER = true;
        setTimeout(auto_save, 100);
    });

    $(":input").on('input', function(){
        busy_ajax("Saving Feedback");
        GLOBAL_FEEDBACK_STATUS = "SAVING";
        TRIGGER = true;
        setTimeout(auto_save, 5000);
    });


    // On Load Scripts ( Same for all pages )    
    $(document).ready(function() { 

        
        // =====================================================================
        // Loading old feedback data from the database
        // =====================================================================

        // alert("CLEAR LOG NOW");
        // AJAX call to reload the state of the fourm
        $.getJSON(
            '../php/load_get_feedback.php',
            { },
            function(e)
            {
                // alert("READ LOG NOW");
                //console.log(e);
                var form = document.getElementById("fb_load_form");

                console.log(e);

                if(e['include'] == "true"){
                    form.include.checked = true;

                }else{
                    form.include.checked = false;

                }

                if(e['rpi_affilate'] != null)
                {
                    
                    form.rpi_affilate.value = e['rpi_affilate'];
                    
                    // If they are affilated with RPI
                    if(e['rpi_affilate'] == "true" )
                    {
                        show('rpi_affilate_div');
                        form.affiliation.value = e['affiliation'];
                    }                   
                }
                
                if (e['arch_edu'] != "" ){ form.arch_edu.value =  e['arch_edu']; }
                if (e['visual_edu'] != "" ){ form.visual_edu.value =  e['visual_edu']; }
                if (e['arch_exp'] != "" ){ form.arch_exp.value =  e['arch_exp']; }
                if (e['visual_exp'] != "" ){ form.visual_exp.value =  e['visual_exp']; }
                
                if(  e['software_list_str'] != null )
                {
                    // For each check that box using ids
                    var s_list = e['software_list_str'].split(',');
                    //console.log(s_list);
                    for(var i = 0;  i < s_list.length; i++){
                        document.getElementById(s_list[i]).checked = true;
                    }
                    
                }

                
                if (e['other_software'] != "" ){ form.other_software.value =  e['other_software']; }
                if (e['software_exp'] != "" ){ form.software_exp.value =  e['software_exp']; }
                if (e['other_exp'] != "" ){ form.other_exp.value =  e['other_exp']; }
                
                if(e['color_blind'] != null)
                {
                    form.color_blind.value = e['color_blind'];
                }   
                
                if (e['user_email'] != "" ){ form.user_email.value =  e['user_email']; }

                 window.ribbon1.enableRibbon(); // to prevent quickly switching tabs &  losing feedback
            }
        );
        
        // // bind 'myForm' and provide a simple callback function 
        // $('#fb_load_form').ajaxForm(function() { 
        //     alert("Debug: Feedback sent to Database"); 
        //     // prevents default redirection
        //     return false;
        // }); 
        
        
        // Prevent default action of "enter" keypress
        $("#fb_load_form").bind("keypress", function (e) {
            if (e.keyCode == 13) {
                $("#btnSearch").attr('value');
                // alert("enter pressed");
                //add more buttons here
                return false;
            }
        });

    }); // onload

    // Helper functions for conditional questions
    function show(id) 
    {
        $("#"+id).show();
    }

    // Helper function for conditional questions
    function hide(id)
    {
        $("#"+id).hide();
    }

    $("#fb_load_form").bind("keypress", function (e) {
        if (e.keyCode == 13) {
            $("#btnSearch").attr('value');
            // alert("enter pressed");
            //add more buttons here
            return false;
        }
    });
    
</script>
