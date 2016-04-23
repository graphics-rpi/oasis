<?php session_start(); ?>

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
    <!--Bootstrap and related libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.5/clipboard.min.js"></script>
    
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