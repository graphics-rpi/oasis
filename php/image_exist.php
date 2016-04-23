<?php

// check if
if(file_exists ($_POST['image'])){

  echo json_encode(array("data" => "true" ));

}else{

  echo json_encode(array("data" => "false" ));

}


?>
