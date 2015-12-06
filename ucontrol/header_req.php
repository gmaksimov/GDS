<?php
/**
 * Includes required pages and variables
 */

ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
require_once('../constants.php');
require_once('../functions.php');
connect_to_mysql(); //from functions.php
require_once('session.php');

if(isset($_SESSION['MSG']) && $_SESSION['MSG'] != NULL){
	$MSG = $_SESSION['MSG'];
	unset($_SESSION['MSG']);
}else{
	$MSG = array();
}

$_SESSION['MSG'] = array();