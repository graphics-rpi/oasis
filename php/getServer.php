<?php

session_start();

$server = gethostname();

// Generate json object and send this along to render_model.js
echo array("result" => $server);

?>