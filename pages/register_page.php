
<?php 
  session_start(); 
  require_once('../php/user.php'); 
  require_once('../php/model.php'); 
?>

<head>

  <script src="../js/lib/jquery.js"></script>

  <link rel="stylesheet" type="text/css" href="../css/register.css">

  <title>OASIS</title>

</head>

<body>

  <div id="register">
    <h1>Register</h1>

    <form action="/php/register.php" method="post"> 

      <input name="email" type="text" placeholder="Username" />
      <input name= "password1" type="password" placeholder="Create Password" />
      <input name= "password2" type="password" placeholder="Retype Password" /><br>


      <div style="font-size:80%;">
        <span style="text-align: justify; text-justify: inter-word;">
          <!--UPDATE ME-->
          This application is a research project for architectural modeling and daylighting simulation. 
          Your feedback is important to help us improve this tool. <br>
          <a id="link" href="#">Click here for more information </a>

          <div id="extra_info" style="display:none;">
            <br>
            <br>
            <br>
            <p>Participation is voluntary. We anticipate no risk or discomfort beyond routine use of a computer and the Internet. <br><br></p>
            <p>Construction of a model averages 5-10 minutes, depending on the complexity and depth of analysis. Your models and written feedback will be collected for use in future publications and the improvement of our tool. <br><br></p>
            <p>No personal information is collected during the registration process. 
            If you choose to provide an email address, researchers may contact you with optional follow-up questions.
            We will not share this email with anyone.
            <br><br></p>

            <p>There is no remuneration offered for participation in this study. You retain ownership of the architectural models designed in our system.<br><br></p>
            For questions or concerns please contact: <br> <br>

            <address>
              Barbara Cutler <a href="mailto:cutler@cs.rpi.edu">cutler@cs.rpi.edu</a>.<br> 
              Phone: 518-276-3274<br>
              Rensselaer Polytechnic Institute <br>
            </address> <br>
            
            <address>
              Max Espinoza <a href="mailto:espinm2@rpi.edu">espinm2@rpi.edu</a>.<br> 
              Rensselaer Polytechnic Institute <br>
            </address> <br>

            <address >
              Chair, Institutional Review Board<br> 
              Rensselaer Polytechnic Institute <br>
              CII 9015110 8th Street<br>
              Troy, NY 12180 <br>
              (518) 276-4873<br>
            </address>
            
            <br>
          </div>

      
        </span><br>

        </label><br>
        <input type="checkbox" value="true" name="include" checked /><label for="include"> I am 18 years or older and give permission for my models and feedback to be used in future publications (Optional) </label>
        <br> <br>
      </div>

      <input type="submit" value="Submit" />
      <br> <br>
      <center><input type="button" value="Already Registered?" onclick= 'location.href = "/pages/login_page.php"'/>
      </center>
      <?php if (isset($_SESSION['error'])){ echo $_SESSION['error']; }?>

    </form>
  </div>

  <center><font color = "ffffff">Browers supported:</font> 
  <br>
  <img src="/images/chrome_icon.png"> <img src="/images/firefox_icon.png"> </center>


</body>


<script>

var REG_MORE_INFO_TOGGLE = false;

// Wait for the page to load first
window.onload = function() {

  //Get a reference to the link on the page
  // with an id of "mylink"
  var a = document.getElementById("link");

  //Set code to run when the link is clicked
  // by assigning a function to "onclick"
  a.onclick = function() {
    if( REG_MORE_INFO_TOGGLE ){
      document.getElementById('extra_info').style.display = 'none';
      REG_MORE_INFO_TOGGLE = false;
    }else{
      document.getElementById('extra_info').style.display = 'inline';
      REG_MORE_INFO_TOGGLE = true;
    }
    return false;

  }

}

</script>
