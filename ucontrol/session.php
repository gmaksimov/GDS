<?php
$lifetime = 60 * 10; //in seconds
session_start();
setcookie(session_name(),session_id(),time()+$lifetime);


function end_session(){
	// Initialize the session.
	// If you are using session_name("something"), don't forget it now!
	session_start();

	// Unset all of the session variables.
	$_SESSION = array();

	// If it's desired to kill the session, also delete the session cookie.
	// Note: This will destroy the session, and not just the session data!
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
		$params["path"], $params["domain"],
		$params["secure"], $params["httponly"]
		);
	}

	// Finally, destroy the session.
	session_destroy();
}

//require_once('../constants.php');
//global $root_login;
//global $root_psw;

if(isset($_POST['unlogin'])){
	end_session();
}

//You already have login
if(isset($_SESSION['ucontrol']['login']))
return;

//loggining
if(isset($_POST['login']) || isset($_POST['password'])){
	if($root_login == $_POST['login'] && $root_psw == $_POST['password']){
		$_SESSION['ucontrol']['login'] = $root_login;
		header("Location: {$_SERVER['REQUEST_URI']}");
	} else {
		$login = "";
	}
}

//login and password mismatch
if(isset($login) && $login == ""){
	require_once('header.php');
	include('../login_page.html');
	my_die("Неверный логин или пароль", "warning");
}

//no login given
if(!isset($login)){
	require_once('header.php');
	include('../login_page.html');
	my_die("Нужно войти в систему", "info", "");
}
