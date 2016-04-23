<?php
// This script is called to toggle between false color visualization and 
// normal visualization.
// This script changes the view path session variable

// Start session
session_start();

// ===================================================================
// Step 1 ) Find out what visualization mode you are in given view path
// ===================================================================

$view_path = $_SESSION['view_path']; //  ../user_output/d4234324234/model_105/results/sim_1111_1111_clear_fcv/
$view_path_array = explode('/',$view_path); // .., user_output, d4234324234, model_105, results, sim_1111_1111_clear_fcv, ''



array_pop($view_path_array);             // pop ''
$sim_path = array_pop($view_path_array); // pop sim_1111_1111_clear_fcv
$sim_path_arr = explode('_',$sim_path);  // sim, 1111, 1111, clear, fcv

$vis_mode = array_pop($sim_path_arr);    // pop fcv or ncv

if($vis_mode == "fcv"){
  array_push($sim_path_arr, "ncv"); // sim, 1111, 1111, clear, ncv
}else{
  array_push($sim_path_arr, "fcv"); // sim, 1111, 1111, clear, fcv
}

$new_sim_path = implode('_',$sim_path_arr); // sim_1111_1111_clear_ncv

array_push($view_path_array,$new_sim_path); //.., user_output, d4234324234, model_105, results, sim_1111_1111_clear_fcv

$new_view_path = implode('/', $view_path_array); // ../user_output/d4234324234/model_105/results/sim_1111_1111_clear_fcv

$new_view_path = $new_view_path . '/' ; // trailing forward slash


$_SESSION['view_path'] = $new_view_path;
error_log("new_view_path -> " . $new_view_path);


?>
