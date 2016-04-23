<?php

// This file returns the username and foldername of the current users so that
// sketching.js knowns where to look to load in previous models
session_start();
require_once('user.php');
require_once('model.php');

// Where we will package our data
$data_array = array();

// Getting user object
$userobj = unserialize($_SESSION['user']);

// Getting folder name
$workingModel = $userobj->workingModel;

// Generate json object needed by sketchui
// to find user folder
echo json_encode(array(  
	"username" => $userobj->username,
	"stat"   => $userobj->workingModel->status,
	"title"    => $userobj->workingModel->title,
	"paths_txt"      => $userobj->workingModel->paths_txt,
    "wallfile_text"  => $userobj->workingModel->wallfile_txt,
	"user_model_num" => $userobj->workingModel->user_model_num,
	"user_renov_num" => $userobj->workingModel->user_renov_num ));
?>
