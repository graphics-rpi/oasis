
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
      <input name= "password2" type="password" placeholder="Retype Password" />


      <div style="font-size:80%;">
        <span style="text-align: justify; text-justify: inter-word;">
          <!--UPDATE ME-->
          <p>
          This application is a research project for architectural modeling and daylighting simulation. 
          Your feedback is important to help us improve this tool.</p><br>
          <!--<a id="link" href="#">Click here for more information </a>

          <div id="extra_info" style="display:none;">-->
          <div id="extra_info">
            
            <p>Participation is voluntary. We anticipate no risk or discomfort beyond routine use of a computer and the Internet. </p><br>
            <p>Construction of a model averages 5-10 minutes, depending on the complexity and depth of analysis. 
            Your models and written feedback will be collected for use in future publications and the improvement of our tool. </p><br>
            <p>No personal information is collected during the registration process. If you choose to provide an email address, 
            researchers may contact you with optional follow-up questions. We will not share this email with anyone.</p><br>

            <p>For the next few weeks we are offering the
            following incentive for current RPI students or recent RPI alums to
            participate in the study.  On June 30th we will have a random drawing
            for twenty $50 prepaid VISA gift cards.  Each user study participant
            will earn 1 entry into the drawing per different RPI dorm room model
            created, with a maximum of 5 entries (for making 5 or more dorm room
            models).  To be entered into the drawing, you must have been a
            student at RPI during the fall 2015 or spring 2016
            semesters and you must provide your RPI RCS email address.</p>

            <input name= "realEmail" type="email" placeholder="Email (Optional)"/>
            <p>Your decision to not participate will not affect your course grade or
            any other academic outcome.  You have the right to terminate your
            participation at any time without penalty or loss of benefits to which
            you are otherwise entitled.  You may chose to not answer any of the
            questions below.</p>
            <br>
            
            <p>You retain ownership of the architectural models designed in our
            system.</p><br>
            For questions or concerns please contact: <br>

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
              (518) 276-4873
            </address><br>
          </div>
        </span>

        </label>
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


<!--<script>

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

</script>-->
