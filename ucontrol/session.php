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

require_once('../constants.php');
global $root_login;
global $root_psw;

if($_POST['unlogin']){
  end_session();
}

if(!$_SESSION['ucontrol']['login'] || $_SESSION['ucontrol']['login'] != $root_login){
  if($_POST['login']){
    if($_POST['login'] == $root_login
    && $_POST['password'] == $root_psw){
      $_SESSION['ucontrol']['login'] = $root_login;
    } else {
      show_message("Login password mismatch", "error");
    }
  }
  if(!$_SESSION['ucontrol']['login']){
    echo
    "<form method=POST>
    	Логин:<input type=text name=login><br>
    	Пароль:<input type=password name=password><br>
   	<input type=submit value=Войти>
    </form>";
    my_die("not logged in");
  }
}
echo "<form method=POST>
<input type=submit value=end_session name=unlogin>
</form>";
