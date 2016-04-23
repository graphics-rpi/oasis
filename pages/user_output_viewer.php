<?php 
  require_once('../php/config.inc.php');          
?>

<html>
<head>

	<title> User Output Viewer Alpha </title>

	<!--Required JS-->
    <script src="../js/lib/jquery.js"></script>
    <script src="../js/lib/raphael.js"></script>
    <script src="../js/lib/raphael.free_transform.js"></script>
    <script src="../js/sketching_ui.js"></script>
    <script src="../js/util.js"></script>

	<!--viewer2d-->
	<script src="../js/output_viewer_aux.js"></script>

</head>
<body>
	<h1>User Output Viewer Alpha</h1>
	<div id="container"> </div>
	<script>
    display_all_renov("mmespinoza1@gmail.com", 4);
		// raphael_model_viwer("container",284,200); // This is my apt model
	</script>
</body>
</html>


