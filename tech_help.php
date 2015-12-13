<?php
/**
 * This is page for feedback from users
 */

require('header_req.php');

include('header.php');

//send message to admin
if(isset($_POST['message']) && $_POST['message'] != NULL){
	$message = addslashes($_POST['message']);
	$format = "j.n.Y G:i:s";
	$date = date($format);

	$sql = "INSERT INTO Messages (Addressee, Sender, Message, Date, Viewed) VALUES ('admin', '$login', '$message', '$date', '1')";
	if($mysqli->query($sql)){
		show_message("Ваше сообщение отправлено", "ok");
		header("Location: {$_SERVER['REQUEST_URI']}");
	}else{
		my_die("Ошибка отправки сообщения: ".$mysqli->error);
	}
}

?>

У вас возникла ошибка? Напишите админу:
<form method=POST>
	<textarea name=message></textarea>
	<br> <input type=submit value=Отправить>
</form>
<hr>
Сайт сделан
<a href="mailto:maksimov.grisha@gmail.com">Максимовым Григорием</a>
и
<a href="mailto:dooku97@mail.ru">Мустафиным Ильгизом</a>
