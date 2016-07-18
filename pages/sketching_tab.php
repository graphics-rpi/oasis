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
    "sketching_tab.php",                   // script
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

    <!-- libraries used for the sketching interface -->
    <script src="../js/lib/raphael.js"></script>
    <script src="../js/lib/ndollar.js"></script>
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/lib/spin.min.js"></script>
    <script src="../js/objFileContents.js"></script>
    <script src="../js/util.js"></script>
    <script src="../js/random_word.js"></script>

    <!--Bootstrap and related libraries -->
    <link rel="stylesheet" href="../css/bootstrap.min.css" />
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/lib/bootbox.min.js"></script>
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
      <div id = 'testingthis' class="my-custom-styles-tool" data-tool-name="my-custom-styles">
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
                        $('#bug_report')[0].reset();
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
        var  GLOBAL_RIBBON_TAB     = "tab-sketch";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-sketch");
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

    <!-- comment out this div to remove sketching functionality -->
    <div class="modal fade in" id="chooseType" data-backdrop="static" data-keyboard="false" role="dialog"
      tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="myModalLabel">Choose your drawing type</h4>
          </div>
          <div class="modal-body">
            <input type="button" class="sketchButton oldstyle" id="oldbutton" value="Classic">
            <input type="button" class="sketchButton sketching" id="newbutton" value="Sketching">
          </div>
        </div>
      </div>
    </div>

    <div id="container-parent" class="sketch_tab_parent">
      <div id="maincontent">
          <div id="container"> 
            <!--Loading virtual tabletop and models-->
            <script src="../js/sketching_ui.js"></script>
          </div><!--container-->
          <div id="sketchpad">
            <script src="../js/contextmenu.js"></script>
            <script src="../js/sketchpad.js"></script>

            <ul id="menuRect" class="contextMenu">
              <li>Reclassify this Object</li>
              <li class="separator"><a type="radio" name="type" val='bed' href="#size_64">Bed</a></li>
              <li><a type="radio" name="type" val='desk' href="#size_100">Desk</a></li>
              <li><a type="radio" name="type" val='skylight' href="#size_130">Skylight</a></li>
              <li><a type="radio" name="type" val='wardrobe' href="#size_130">Wardrobe</a></li>
            </ul>

          </div> <!--container-->
      </div><!--maincontent-->

      <div id="sidecontent">

        <div id= "title">
          <form id="title_frm"> 
          <p><b>Model Name</b>
          <input id="model_title" name="title" type="text" maxlength="100" value="Loading Title..." style="text-align: center"> 
          </p>
          </form>
          <div id="dev_info"> </div>
        </div><!--title-->

        <div id="feedback" class="feedback">
          <div class="dontsee">
          <label class="switch">
            <input id="togglecanvas" type="checkbox">
            <div class="slider"></div>
          </label>
        </div>

          <form id="fb_sketch_form" action="../php/sketch_submit_feedback.php" method="post">
            
            <!-- TODO: Figure out what to do with model titles
                       NO DUPLICATES PLEASE 
            <p>What is a good name for this model? <br>
            <input type="text" name="model_name"> </p>
            -->

            <p>Select the category of this model: <br>
            <select name="category" id="category" onchange="check_other()">
              <option value="">Select</option>
              <option value="bedroom">Bedroom</option>
              <option value="dorm">Dorm</option>
              <option value="living_room">Living room </option>
              <option value="apartment_house">Apartment / House</option>
              <option value="classroom">Classroom</option>
              <option value="office">Office</option>
              <option value="lobby">Lobby</option>
              <option value="other">Other</option>
            </select></p>

            <div id='other_category' style="display:none;">
              <p>What is the category of this model?<br>
              <input type="text" name="unlisted_category"> </p>
            </div>

            <div id="rpi_affilate" style="display:none;">

              <p>What dorm is this a model of? <br>
                <select name="dorm" id="dorm" onchange="check_other_dorm()">
                  <option value="">Select</option>
                  <option value="barh">BARH (Burdett Avenue Residence Hall)</option>
                  <option value="barton">Barton Hall</option>
                  <option value="rahp_apt">Beman Lane Undergraduate RAHP Apartments</option>
                  <option value="blitman">Blitman Residence Commons</option>
                  <option value="bray">Bray Hall</option>
                  <option value="bryckwyck">Bryckwyck Floor Plans</option>
                  <option value="cary">Cary Hall</option>
                  <option value="colonie">Colonie Apartments</option>
                  <option value="commons">Commons</option>
                  <option value="crockett">Crockett Hall</option>
                  <option value="davison">Davison Hall</option>
                  <option value="e_complex">E-Complex</option>
                  <option value="hall">Hall Hall</option>
                  <option value="nason">Nason Hall</option>
                  <option value="north">North Hall</option>
                  <option value="nugent">Nugent Hall</option>
                  <option value="quad">Quadrangle (The Quad)</option>
                  <option value="sharp">Sharp Hall</option>
                  <option value="rahp_single">Single RAHP</option>
                  <option value="stacwyck">Stacwyck Apartments</option>
                  <option value="warren">Warren Hall</option>
                  <option value="other">Other</option>
                </select>

              <div id='other_dorm' style="display:none;">

               <p> Is this a RPI affilated Dorm? <br>
                 <input type="radio" name="is_rpi_dorm" value="true">Yes
                 <input type="radio" name="is_rpi_dorm" value="false">No<br>
               </p>
 
                <p>What dorm is this a model of? <br>
                <input type="text" name="unlisted_dorm"> </p>

              </div>

              <p>[Optional] What floor were you on?<br>
              <input type="number" name="floor" min="1" max="10"></p>

              <p>[Optional] What was the room number?<br>
              <input type="number" name="room" min="1" max="100"></p>

            </div>

              <p> When was the last time you visited this space? <br>
              <select name="visited" id="visited">
                <option value="">Select</option>
                <option value="week">Less than a week ago</option>
                <option value="month">Less than a month ago</option>
                <option value="year">Less than a year ago</option>
                <option value="four_years">Less than 4 years ago</option>
                <option value="more_years">More than 4 years ago</option>
                <option value="new_design">Never, this is a new design</option>
                <option value="never_visited">Never, I haven't visited this space</option>
              </select></p>

              <p> How often did you visit this space? <br>
              <select name="frequency" id="frequency">
              <option value="">Select</option>
              <option value="once">Once</option>
              <option value="occasionally ">Occasionally </option>
              <option value="multiple">Multiple times a week</option>
             <option value="new_design">Never</option>
              </select></p>

              <p> How confident are you in modeling this space?(dimensions, orientation,furniture layout)<br>
               <input type="radio" name="confidance" value="5"> 5 (very confident)<br>
               <input type="radio" name="confidance" value="4"> 4 <br>
               <input type="radio" name="confidance" value="3"> 3 <br>
               <input type="radio" name="confidance" value="2"> 2 <br>
               <input type="radio" name="confidance" value="1"> 1 (unsure)<br>
               </p>
           

            <p>Any additional information about the model you would like to share? <br>
            <textarea name="comments" rows="3" cols="50"></textarea></p>

            <hr>

            <p>What did you find fun or interesting in this sketching environment?<br>
            <textarea name="interesting" rows="3" cols="50"></textarea></p>

            <p>What additional features should be added to the system to allow greater flexibility in design?<br>
            <textarea name="features" rows="3" cols="50"></textarea></p>


            <p>Describe some designs that you were not able to create due  to system limitations:<br>
            <textarea name="limitations" rows="3" cols="50"></textarea></p>

            <p>Was there anything you did not like about working in this sketching environment?<br>
            <textarea name="dislikes" rows="3" cols="50"></textarea></p>

            <p>Where there any elements of the user interface that were hard to use or confusing?<br>
            <textarea name="ui" rows="3" cols="50"></textarea></p>
          </form><!--form-->
        </div><!--feedback-->

        <div id="help" class="help_tab">
          <div id="helplinks">
            <h2>Help</h2>
            <a href="#" onclick="helpcontents('wall')" >How to draw a wall</a>
            <br>
            <a href="#" onclick="helpcontents('window')">How to draw a window</a>
            <br>
            <a href="#" onclick="helpcontents('skylight')">How to place a skylight</a>
            <br>
            <a href="#" onclick="helpcontents('furniture')">How to place a bed, desk, or wardrobe</a>
            <br>
            <a href="#" onclick="helpcontents('deleteobj')">How to delete/remove an object</a>
            <br>
            <a href="#" onclick="helpcontents('orientation')">How to change the orientation of your room</a>
            <br>
            <a href="#" onclick="helpcontents('location')">How to change the location of your room in the world</a>
            <br>
            <a href="#" onclick="helpcontents('done')">What do I do after I'm done building my room?</a>
          </div>

          <hr>

          <div id="helpcontents">

              <div id="wall" class="helpcontent">
                To draw a wall first click on
                <img src="../pages/AcidJs.Ribbon/icons/large/wall.png">.
                Then click and hold the mouse inside of the canvas space. Drag and release the mouse to define the length and position of the wall.
                <br>
                <center> <img src="../images/walls.gif" height="150" width="150" ></center>
              </div>

              <div id="window" class="helpcontent">
                To draw a window first click on 
                <img src="../pages/AcidJs.Ribbon/icons/large/window.png">.
                Then click and drag your cursor over an existing wall to define the length and placement of a window. 
                <br>
                <center> <img src="../images/windows.gif" height="150" width="150" ></center>
              </div>

              <div id="skylight" class="helpcontent">
                To place a skylight first click on 
                <img src="../pages/AcidJs.Ribbon/icons/large/skylight.png">.
                Then click and drag the skylight to move it around the canvas.
                Use the white handles to rotate and change the size of the skylight.
                <center> <img src="../images/skylight.gif" height="150" width="150" ></center>
              </div>

              <div id="furniture" class="helpcontent">
                To place furniture into the room click on a furniture item such as:
                <img src="../pages/AcidJs.Ribbon/icons/large/bed.png">
                <img src="../pages/AcidJs.Ribbon/icons/large/desk.png">
                <img src="../pages/AcidJs.Ribbon/icons/large/wardrobe.png">
                Then click and drag the item to move it around the canvas.
                Use the white handles to rotate.
                <br>
                <center> <img src="../images/furniture.gif" height="150" width="150" ></center>
              </div>

              <div id="deleteobj" class="helpcontent">
                To remove any item click on 
                <img src="../pages/AcidJs.Ribbon/icons/large/remove.png">.
                Then click on the item you would like to remove.
                <br>
                <center> <img src="../images/remove.gif" height="150" width="150" ></center>
                
              </div>
              <div id="orientation" class="helpcontent">
                To change the orientation of your building/room first click on
                <img src="../pages/AcidJs.Ribbon/icons/large/compass.png">.
                Then click and drag anywhere on the canvas to change the North and South directions.
                <br>
                <center> <img src="../images/compass.gif" height="150" width="150" ></center>

              </div>
              <div id="location" class="helpcontent">
                To change the geographic location of your design first click on 
                <img src="../pages/AcidJs.Ribbon/icons/large/globe.png">.
                Then click anywhere on map to approximate your location.<br>
              </div>
              <div id="done" class="helpcontent">
                When you're done, click on <b>Step 3: Generate a 3D Model</b> tab.
              </div>
          </div>

          <input id="xbutton" type="button" value="X" onclick="hidehelp()">
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

      </div><!--sidecontent-->

    </div><!--container-parent-->

    <div id="status_pane"> Ready </div>

</body>

<script>


$(window).bind('resize', function(e)
{
  if (window.RT) clearTimeout(window.RT);
  window.RT = setTimeout(function()
  {
    global_button_handler("tab-switch-sketching:" + "../pages/sketching_tab.php");
  }, 100);
});
    
// On Load Scripts ( Same for all pages )    
$(document).ready(function() {
  // ==================================================
  // Loading in previously entered feedback
  // ==================================================
  //alert("CLEAR LOG");

    var TRIGGER = false;

    // Auto save function
    function auto_save(){

        if( TRIGGER ){
          save_form('fb_sketch_form');
          // ready_ajax("Feedback Saved")
          TRIGGER = false;
        }
    }

    $("#fb_sketch_form").change(function(){
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
    '../php/sketch_get_feedback.php',
    {},
    function(e)
    {

      //alert("GOTTEN FEEDBACK");

      //console.log(e);

      var form = document.getElementById("fb_sketch_form");

      // Model Related Responces
      // ==================================

      if (e['category'] != "")
      {

        form.category.value = e['category'];

        if (e['category'] == "dorm")
        {

          if (e["rpi_affilate"] == "true")
          {

            // If they had a dorm, display and reload these
            show("rpi_affilate");

            // Which dorm?
            if (e['dorm'] != "")
            {

              form.dorm.value = e['dorm'];

              // If they chose an unlisted dorm
              if (e['dorm'] == "other")
              {

                show("other_dorm");

                if(e['is_rpi_dorm'] != ""){
                  form.is_rpi_dorm.value = e['is_rpi_dorm'];
                }

                if (e['unlisted_dorm'] != "")
                {
                  form.unlisted_dorm.value = e['unlisted_dorm'];
                }
              }

              if (e['floor'] != "")
              {
                form.floor.value = e['floor'];
              }

              if (e['room'] != "")
              {
                form.room.value = e['room'];
              }

              
            }


          }
        }
        else if (e['category'] == "other")
        {

          show("other_category");

          if (e['unlisted_category'] != "")
          {
            form.unlisted_category.value = e['unlisted_category'];
          }
        }
      }

      if (e['visited'] != "")
      {
        form.visited.value = e['visited'];
      }

      if (e['frequency'] != "")
      {
        form.frequency.value = e['frequency'];
      }

      if (e['confidance'] != "")
      {
        form.confidance.value = e['confidance'];
      }

      if (e['comments'] != "")
      {
        form.comments.value = e['comments'];
      }


      // User Related Responces
      // ====================================
      if (e['interesting'] != "")
      {
        form.interesting.value = e['interesting'];
      }

      if (e['features'] != "")
      {
        form.features.value = e['features'];
      }

      if (e['limitations'] != "")
      {
        form.limitations.value = e['limitations'];
      }

      if (e['dislikes'] != "")
      {
        form.dislikes.value = e['dislikes'];
      }

      if (e['ui'] != "")
      {
        form.ui.value = e['ui'];
      }
      
      window.ribbon1.enableRibbon(); // to prevent quickly switching tabs &  losing feedback      
    }
  );

  // Killing enter key on feedback form
  // Required on all tab templates
  // $("#fb_sketch_form").bind("keypress", function(e)
  // {
  //   // alert("Triggered bind keypres to feedback form");

  //   GLOBAL_SKETCH_ALTERED = true; // user belives in this!

  //   if (e.keyCode == 13)
  //   {
  //     $("#btnSearch").attr('value');
  //     return false; // prevents default actions
  //   }
  // });

  // What happens when you press enter on title page
  $("#title_frm").bind("keypress", function(e)
  {
    GLOBAL_SKETCH_ALTERED = true; // we changed something about the sketch
    if (e.keyCode == 13)
    {
      $("#btnSearch").attr('value');
      return false; // prevents default actions
    }
  });

  // $("#fb_sketch_form").click(function()
  // {
  //   //alert("Triggered click");
  //   // GLOBAL_SKETCH_ALTERED = true; // we changed something about the sketch
  // });

  // If someone changed something in the title page
  $("#model_title").change(function()
  {
    GLOBAL_SKETCH_ALTERED = true; // we changed something about the sketch
  });

  $("#model_title").focus(function()
  {
    GLOBAL_SKETCH_ALTERED = true; // we changed something about the sketch
  });

  // console.log('test1');
  // load_model();
  // load_sketch_sketchpad();
  // console.log('test2');
  // if(IS_NEW_MODEL == 0){
  //   $('#chooseType').modal('show');
  // }
  // else if(IS_NEW_MODEL == 1){

  // }
  // else if(IS_NEW_MODEL == 2){
  //     $("#container").toggle();
  //     $("#sketchpad").toggle();
  //     load_model();
  // }

}); // onload


function check_other_dorm()
{
  if ('other' == document.getElementById('dorm').value)
  {
    show("other_dorm");
  }
  else
  {
    hide("other_dorm");
  }
}

function check_other()
{
  if ('other' == document.getElementById('category').value)
  {
    show("other_category");
  }
  else
  {
    hide("other_category");
  }
  // TODO Ajax call to see if they are an RPI affiliate
  if ('dorm' == document.getElementById('category').value)
  {
    show("rpi_affilate");
  }
  else
  {
    hide("rpi_affilate");
  }
}


function show(id)
{
  $("#" + id).show();
}

function hide(id)
{
  $("#" + id).hide();
}

// $("#sketchpad").hide();
$("#sketchpad").toggle();
$("#togglecanvas").click(function() {
  $("#container").toggle();
  $("#sketchpad").toggle();
  load_model();
});

$('#oldbutton').click(function(){
  $('#chooseType').modal('hide');
});
$('#newbutton').click(function(){
  $('#chooseType').modal('hide');
  $("#container").toggle();
  $("#sketchpad").toggle();
  ribbon1.disableTools(["button-straight-wall", "button-straight-window", "button-skylight",
    "button-bed", "button-desk", "button-closet", "button-remove", "button-change-orientation"]);
});

//////////////////////////////////////////////////////////////////////////////////////


</script>
