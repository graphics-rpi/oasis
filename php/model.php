<?php

// A class to store the model (walls and related info)
class Model{
 
// Fields
var $id, 
    $title,
    $user_model_num, 
    $user_renov_num, 
    $wallfile_txt, 
    $paths_txt,
    $status;

  function Model($id, $title, $user_model_num, $user_renov_num, $wallfile_txt, $paths_txt){

    $this->id                     = $id;
    $this->title                  = $title;
    $this->user_model_num         = $user_model_num;
    $this->user_renov_num         = $user_renov_num;
    $this->wallfile_txt           = $wallfile_txt;
    $this->paths_txt              = $paths_txt;
    $this->status                 = 'Not Set';

  }//construct

  function setStatus($str) { $this->status = $str; }


}//model
?>
