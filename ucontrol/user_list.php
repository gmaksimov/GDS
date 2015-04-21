<?php
require('header.php');

//delete user
if(isset($_POST['del_user'])){
    $dpid = $_POST['dpid'];
    $sql = "DELETE FROM Users WHERE PID=$dpid";
    $result = $mysqli->query($sql) OR show_message("Не смог удалить юзверя с PID($dpid)", "error");
    if($mysqli->error != ""){
        show_message($mysqli->error, "debug");
    }
}

//add new user
if(isset($_POST['add_user'])){
  $found = 0;
  while(!$found){
    $login = uniqid();
    $sql = "SELECT Login FROM Users WHERE Login='$login'";
    $result = $mysqli->query($sql) OR my_die($mysqli->error);
    if($result->num_rows == 0)
      $found = 1;
  }
  $sql = "INSERT INTO Users (Login, Pass, Language) VALUES ('$login', 'pas', 'English')";
  $result = $mysqli->query($sql) OR show_message("Error creating user: ".$mysqli->error, "error");
  $last_id = $mysqli->insert_id;
  header("Location: edit_user.php?pid=$last_id");
}

//save user edition
if(isset($_POST['login']) && isset($_POST['password']) && isset($_POST['pid']) && isset($_POST['privilegies']) && isset($_POST['mail'])){
  $login = $_POST['login'];
  $password = $_POST['password'];
  $pid = $_POST['pid'];
  $privilegies = $_POST['privilegies'];
  $mail = $_POST['mail'];
  $sql = "UPDATE Users SET Login='$login', Pass='$password', Privilegies='$privilegies', Mail='$mail'
  WHERE PID='$pid'";
  if(!$mysqli->query($sql)){
    show_message("Error saving: ".$mysqli->error);
  } else {
    header("Location: user_list.php");
  }
}
//draw teh table
echo"<div style='display:inline-table;padding-right:100px'>";
$sql = "SELECT * FROM Users";
$result = $mysqli->query($sql) OR my_die($mysqli->error);
echo "<table border=1>
<tr><th>Login</th><th>Pass</th><th>Privilegies</th><th>Mail</th><th>&nbsp</th>";
while($row = $result->fetch_array()){
  $login = $row['Login'];
  $password = $row['Pass'];
  $pid = $row['PID'];
  $privilegies = $row['Privilegies'];
  $mail = $row['Mail'];
  echo "
  <tr>
  <td>$login</td><td>$password</td><td>$privilegies</td><td>$mail</td>
  <td><a href=edit_user.php?pid=$pid>Edit</a></td>
  <td>
    <form method=POST>
        <input type=text value='$pid' hidden=hidden name='dpid'>
        <input type=submit value='Удалить' name=del_user>
    </form>
  </td>
  </tr>
  ";
}
echo "</table><form method=POST> <input type=submit value=add_user name=add_user> </form></div>";
include('show_tasks_list.php');


include('footer.php');
	