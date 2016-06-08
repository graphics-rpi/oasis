<!DOCTYPE html>
<html lang="en">
	<head>
		<script src="../../js/lib/raphael.js"></script>
		<script src="../../js/lib/jquery.js"></script>
    	<script src="../../js/lib/jquery.form.min.js"></script>

    	<script src="../js/utilities_new.js"></script>

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
							Name <input type="text" name="name" id="name"><br>
							Describe what is wrong: <input type="text" name="desc" id="desc"><br>
							<input type="submit" value="submit"></input>
						</form>
					</div>
					<div class="underpanelbox">
						<textarea rows="4" cols="20" id="textplace"></textarea>
						<button id='testinput'>Test it</button>
					</div>
					<div class="underpanelbox">
						North Arrow Direction: <br><span id="northDir">90</span>
					</div>
					<div class="underpanelbox">
						<form id= "export" method="post">
							modelId <input type="text" name="name" id="mId"><br>
							modelName <input type="text" name="mName" id="mName"><br>
							owner <input type="text" name="owner" id="owner"><br>
							<input type="submit" value="Export"></input>
						</form>
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


