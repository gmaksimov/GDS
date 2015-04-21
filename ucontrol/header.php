<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../style.css" type="text/css">
<script type="text/javascript" src="script.js"></script>
<title>GGds uControl</title>
</head>
<body>
<?php 
include('./../functions.php');
require('session.php');

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);


connect_to_mysql(); //from ../functions.php

echo "<a href=user_list.php>список юзеров</a> || <a href=delete_result.php>&#1091;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100; &#1090;&#1077;&#1089;&#1090;&#1099;</a> || <a href=..>GGds</a><hr>";
