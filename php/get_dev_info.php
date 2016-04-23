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

$hashed = $userobj->getUserFolderName();
$id     = $workingModel->id;

// Generate json object needed by sketchui
// to find user folder
echo json_encode(array(  
	"hashed" => $hashed,
	"id"   => $id
	));
?>