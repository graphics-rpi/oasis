<!DOCTYPE html>
<html lang="en">
	<head>
		<script src="../../js/lib/raphael.js"></script>
		<script src="../../js/lib/jquery.js"></script>
    	<script src="../../js/lib/jquery.form.min.js"></script>
    	<script src="../../js/lib/math.min.js"></script>

    	<script src="../js/ndollar.js"></script>
    	<script src="../js/utilities_new.js"></script>

    	<script src="../js/simplify.js"></script>
    	<script src="../js/shortstraw.js"></script>
  		<script src="../js/unistroke.js"></script>
  		<!-- <script src="../js/sketchpad.js"></script> -->
    	<script src="../js/grid.js"></script>

    	<link rel="stylesheet" href="../css/new_sketching.css" />

		<meta charset="utf-8">
		<title>Paint</title>

	</head>
	
	<body>
		<div id="pagecontainer">
			<div id="maincontainer">
				<div class="sidepanel">Objects<p></p><div id="objectOutput"></div></div>
				<div class="sidepanel">Strokes<p></p><div id="strokeOutput"></div></div>				

				<div id="canvas"></div>

				<div id="underpanel">
					<div class="underpanelbox">
						<form id= "error_report" method="post">
							Name/Desc? <input type="text" name="name" id="name"><br>
							What is wrong? <input type="text" name="desc" id="desc"><br>
							<input type="submit" value="submit"></input>
						</form>
					</div>
					<div class="underpanelbox">
						<textarea rows="4" cols="25" id="textplace"></textarea>
						<button id='testinput'>Test it</button>
					</div>
				</div>
			</div>

			<div id="infopanel">
				<div id="one" class="subpanel"></div>
				<div id="two" class="subpanel"></div>
				<div id="three" class="subpanel"></div>
				<div id="four" class="subpanel"></div>
				<div id="five" class="subpanel"></div>
				<div id="six" class="subpanel"></div>
				<div id="seven" class="subpanel"></div>
				<div id="eight" class="subpanel"></div>
			</div>
		</div>
	</body>
	<script src="../js/new_sketching.js"></script>
</html>


