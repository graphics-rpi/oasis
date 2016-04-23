<?php
	
// This file contains functions that will generate the appropipate html widget for inputs
// It is used a helper by get_questions.php	
// They will not return anything but directly echo the html that contains the widget
// All these functions will trigger a default responce when $val == "NaN"	

function generate_scale_radio($string , $val)
{
	// This function will generate scale radio button
	// What would you rate this function?
	// 1 [ ] 2[ ] 3[ ] 4[ ] 5[ ]
	echo "Not implemented yet";
}

function generate_yesno_radio($string , $val)
{
	// This function will generate true and false radio buttons
	// Would you say this was helpful?
	// True [ ] False [ ]
	echo "Not implemented yet";
}

function generate_sm_txt($string , $val)
{
	// This function will generate a small one line input field
	// Biggest problem with the system 
	// <small text field>
	echo "Not implemented yet";
}

function generate_lg_txt($string , $val)
{
	// This function will generate a small one line input field
	// Biggest problem with the system 
	// <lg text field>
	echo "Not implemented yet";
}

?>