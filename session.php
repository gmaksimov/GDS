<?php
$lifetime = 60 * 10; //in seconds
session_start();
setcookie(session_name(),session_id(),time()+$lifetime);

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
}

if(isset($_POST['unlogin'])){
  end_session();
}

if(isset($_SESSION['gds']['login']))
    return;

do if(isset($_POST['login']) || isset($_POST['password'])){
        if($_POST['login'] == "" || $_POST['password'] == ""){
            break;
        }
        $sql = "SELECT * FROM Users WHERE Login='{$_POST['login']}'";
        $result = $mysqli->query($sql) OR my_die("Ошибка: ".$mysqli->error);
        $row = $result->fetch_array();
        $psw = $row['Pass'];
        //die();
        if($psw == $_POST['password']){
            $login = $_POST['login'];
            $_SESSION['gds']['login'] = $login;
            header("Location: {$_SERVER['REQUEST_URI']}");
        } else {
            $login = "";
        }
    }
while(false);

if(!isset($login) || $login == ""){
	require_once('header.php');
	include('login_page.php');
    my_die("Нужно войти в систему", "warning");
}
