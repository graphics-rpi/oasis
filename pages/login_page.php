<?php session_start(); 
require_once('../php/user.php'); 
require_once('../php/model.php'); 
?>

<head>
<script src="/js/lib/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="/css/login.css">
<title>OASIS</title>

</head>

<body>

<div id="login">
  <h1>OASIS</h1>
  <h4><center>Online Architectural Sketching Interface<br>for Simulations</center></h4>

  <form action="/php/login.php" method="post">

    <input name="email"  type="text" placeholder="Username" />
    <input name= "password" type="password" placeholder="Password" />
    <input type="submit" value="Log in" />
    <br> <br>
    <center> <input type="button" value="Register" onclick= "location.href = '/pages/register_page.php'"/> </center>
    <?php if (isset($_SESSION['error'])){ echo $_SESSION['error']; }?>
  </form>
  
</div>

<center><font color = "ffffff">Browers supported:</font> 
<br>
<img src="/images/chrome_icon.png"> <img src="/images/firefox_icon.png"> </center>

</body>
