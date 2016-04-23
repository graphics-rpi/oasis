<?php session_start();?>
<!DOCTYPE html>
<html lang="en">
<head>

<script src="./js/lib/jquery.js"></script>
<meta charset="utf-8">
<title>Oasis Dev</title>

</head>

<body>

<?php
if(!isset($_COOKIE['oasis_developer'])) {
  echo "<h1> Developer Mode: Not Set </h1>";
  echo "<p>Developer Mode is not set, set by clicking the button below, then launch application by clicking but button below</p>";

}else{
  echo "<h1> Developer Mode: Set </h1>";
  echo "<p>Developer Mode is currently set, launch application by clicking but button below</p>";
}
?>

<button type="button" onclick="openTool()">Launch Application</button> <br>

<h2>Actions</h2>

<button type="button" onclick="dev_cookie()">Create Developer Mode Cookie</button> <br>
<button type="button" onclick="delete_cookie()">Delete Developer Mode Cookie</button> <br>

</body>
</html>

<script type="text/javascript">

function dev_cookie(){
	document.cookie="oasis_developer=true";
	location.reload();
}


function openTool(){

	newwindow = window.open("pages/login_page.php", 'newwindow','width=1024,height=800,location=0');

	if(window.focus){newwindow.focus(); }

	return false;

}

function delete_cookie(){
  document.cookie = "oasis_developer=; expires=Thu, 01 Jan 1970 00:00:00 UTC";
  location.reload();
}
	
</script>