<?php

// This file returns the username and foldername of the current users so that
// sketching.js knowns where to look to load in previous models
session_start();
require_once('user.php');
require_once('model.php');

// Getting user object
$userobj = unserialize($_SESSION['user']);
$generated_html = $userobj->prevTaskList();

echo $generated_html;
?>
