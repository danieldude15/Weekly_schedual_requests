<?php
session_start(); 
session_destroy();
include_once 'functions.php';
header('Location: '.$GLOBALS["home_url"]);
?>

