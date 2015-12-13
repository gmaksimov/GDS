<?php
/**
 * This page is for session actions
 */

$lifetime = 60 * 10; //in seconds
session_start();
setcookie(session_name(),session_id(),time()+$lifetime);

/**
 * This function ends session
 * @return void 
 */
function end_session(){
	// Initialize the session.
	// If you are using session_name("something"), don't forget it now!
	//session_start();

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
	header("Location: test_list.php");
}

if(isset($_POST['unlogin'])){
	end_session();
}

//You already have login
if(isset($_SESSION['gds']['login']))
return;

//loggining
do if(isset($_POST['login']) || isset($_POST['password'])){
	if($_POST['login'] == "" || $_POST['password'] == ""){
		break;
	}
	$login = addslashes($_POST['login']);
	$sql = "SELECT * FROM Users WHERE Login='$login'";
	$result = $mysqli->query($sql) OR my_die("Ошибка: ".$mysqli->error);
	$row = $result->fetch_array();
	$psw = $row['Pass'];
	
	if($psw == $_POST['password']){
		$_SESSION['gds']['login'] = $login;
		header("Location: {$_SERVER['REQUEST_URI']}");
	} else {
		$login = "";
	}
}
while(false);

//login and password mismatch
if(isset($login) && $login == ""){
	require_once('header.php');
	include('login_page.html');
	my_die("Неверный логин или пароль", "warning");
}

//no login given
if(!isset($login)){
	require_once('header.php');
	include('login_page.html');
	my_die("Нужно войти в систему", "info", "");
}
