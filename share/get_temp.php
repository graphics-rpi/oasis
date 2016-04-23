<?php



//session_start();

$data_array = array();
error_log('get_view_path.php: view_path: '.$view_path );

// Generate json object and send this along to render_model.js
echo json_encode(array("result" => $view_path ));

?>
