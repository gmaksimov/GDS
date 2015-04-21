<?php
require('header_req.php');
//header
include('header.php');
if($login == 'admin'){}else{
	my_die("Ошибка: у вас нет прав на этот раздел сайта");
}
//send message
if(isset($_POST['send_message']) && isset($_POST['addressee'])){
	$message = $_POST['send_message'];
	$format = "j.n.Y G:i:s";
	$addr = $_POST['addressee'];
	$date = date($format);
	for($i = count($addr) - 1; $i >= 0; $i--){
		$addressee = $_POST['addressee'][$i];
		$sql = "INSERT INTO Messages (Addressee, Sender, Message, Date, Viewed) VALUES ('$addressee', '$login', '$message', '$date', '1')";
		if($mysqli->query($sql)){
			show_message("Сообщение отправлено $addressee", "ok");
		}else{
			show_message("Ошибка отправки сообщения $addressee: ".$mysqli->error, "error");
		}
	}
}
//show body
echo"Выберете получателя (используйте CTRL и/или SHIFT чтобы выбрать больше одного получателя)<br>
<form method=POST>
<select name='addressee' multiple required>";
$sql = "SELECT Login FROM Users WHERE login !='$login' ORDER BY PID";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время выбора получателя: ".$mysqli->error);
while($row = $result->fetch_array()){
	$user = $row['Login'];
		echo"<option value='$user'>$user</option>";
}
echo"</select><br>
Сообщение: <br>
<textarea name=send_message></textarea><br>
<input type=submit value=Отправить>
</form>";
include ('footer.php');