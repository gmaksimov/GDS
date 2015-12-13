<?php
/**
 * This page is for answering to the messages
 */

require('header_req.php');

include('header.php');

if(!isset($_GET['pid'])){
	my_die("Ошибка: Не дан PID");
}

$pid = $_GET['pid'];

//check privacy
$sql = "SELECT PID FROM Messages WHERE Addressee='$login'";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$z = 0;
while($row = $result->fetch_array()){
	if($row['PID'] == $pid){
		$z++;
		break;
	}
}
if($z != 1){
	my_die("Ошибка: у вас нет доступа к этому сообщению");
}

//Make the message viewed
$sql = "UPDATE Messages SET Viewed =0 WHERE PID ='$pid'";
if(!$mysqli->query($sql)){
	show_message("Ошибка просмотра сообщения: ".$mysqli->error);
}

//reply to message
if(isset($_POST['send_message']) && $_POST['send_message'] != NULL){
	$message = $_POST['send_message'];
	$format = "j.n.Y G:i:s";
	$date = date($format);

	$sql = "SELECT * FROM Messages WHERE PID=$pid";
	$result = $mysqli->query($sql) OR my_die("Ошибка выбора отправителя: ".$mysqli->error);
	$row = $result->fetch_array();
	$sender = $row['Sender'];

	$sql = "INSERT INTO Messages (Addressee, Sender, Message, Date, Viewed) VALUES ('$sender', '$login', '$message', '$date', '1')";
	if($mysqli->query($sql)){
		show_message("Сообщение отправлено", "ok");
		header("Location: {$_SERVER['REQUEST_URI']}");
	}else{
		my_die("Ошибка отправки сообщения: ".$mysqli->error);
	}
}

//show body
$sql = "SELECT * FROM Messages WHERE PID=$pid";
$result = $mysqli->query($sql) OR my_die("Ошибка выбора отправителя : ".$mysqli->error);
$row = $result->fetch_array();
$sender = $row['Sender'];
$message = $row['Message'];

echo"
Сообщение от: $sender:
<br>
<textarea>$message</textarea>
<hr>
Ваш ответ для $sender:
<br>
<form method=POST>
	<textarea name=send_message></textarea>
	<br> <input type=submit value=отправить>
</form>";

include('footer.php');