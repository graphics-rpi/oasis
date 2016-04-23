<?php session_start(); ?>

  <head>
    <meta charset="utf-8" />
    <title> Online Contraption </title>

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
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/lib/spin.min.js"></script>
    <script src="../js/objFileContents.js"></script>
    <script src="../js/util.js"></script>



    <!--Loading virtual tabletop and models-->
    <!-- <script src="../js/sketching_ui.js"></script> -->
  </head>

  <body>

    <!-- Ribbon User Interface -->
    <div id="ribbon-ui"> </div>

    <!-- register control's runtime libraries -->
    <script src="./AcidJs.Ribbon/classes/Ribbon.js"></script>

    <!-- / register control's runtime libraries -->
    <!-- loads the event handlers in addition to ribbon -->
    <script src="../js/ribbon_events.js"></script>
    <!-- \loads the event handlers in addition to ribbon -->

    <!-- Where the ribbon is defined -->
    <script src="../js/ribbon_ui.js"></script>


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
        var  GLOBAL_RIBBON_STATE   = "not loaded";
        var  GLOBAL_RIBBON_TAB     = "tab-result";

        window.ribbon1.init(); 

        window.ribbon1.setTabActive("tab-result");
        GLOBAL_RIBBON_STATE = "loaded"; // helps the tab_hanlder

    </script>

    <div id="container-parent">
          
        <div id="feedback" > </div>
    
        <div id="container"> </div>
    
    </div>

    <div id="footer">
        
        <h3>Footer Goes Here</h3>
        
    </div>

</body>

<script>
    
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
