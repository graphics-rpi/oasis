<?php

    $desc = $_POST["desc"];
    $name = $_POST["name"];
    $points = $_POST["points"];
    $date = date("Y/m/d");
    $time = date("h:i:sa");

	//Something to write to txt logs
		$log  = "-------------------------".PHP_EOL.
				"Time Entered: ".$time." ".$date.PHP_EOL.
				"Description of what was wrong: ".$desc.PHP_EOL.
				"Data:".PHP_EOL.
				$points.PHP_EOL;

	$dest = '../error_reports/'.$name.date("_md_").date("hi").'.txt';
	//Save string to log, use FILE_APPEND to append.
	file_put_contents($dest, $log, FILE_APPEND | LOCK_EX);
?>
