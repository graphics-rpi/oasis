<?php

session_start();
require_once('config.inc.php');
require_once('user.php');
require_once('model.php');

// Where we will package our data
$data_array = array();
$view_path = $_SESSION['view_path'];

// Parse  view path to obtain the title from what ever is on the view path
$view_title = "Not Found";

error_log('get_title_from_view_path: '.$view_path);

// Getting id of the model
$id = explode("/",$view_path); // Extracts model_7
error_log('get_title_from_view_path: '.$id);
$id = $id[3];
error_log('get_title_from_view_path: '.$id);
$id = explode("_",$id); // Extracts just 7
error_log('get_title_from_view_path: '.$id);
$id = $id[1];
error_log('get_title_from_view_path: '.$id);

$query = "SELECT title FROM model_meta WHERE id='$id';";
$res = pg_query($query) or die("Couldn't retrive title with id");

$view_title = pg_fetch_row($res);
$view_title = $view_title[0];

// Ship out data to load_sketch_init() in sketching_ui.js

echo json_encode(array("data" => $view_title));

?>

