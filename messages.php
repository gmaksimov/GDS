<?php
require('header_req.php');

//delete message
if(isset($_POST['Dpid'])){
	$Dpid = $_POST['Dpid'];
	$sql = "UPDATE Messages SET Deleted =1, Viewed =0 WHERE PID='$Dpid'";
	if($mysqli->query($sql)){
		show_message("Сообщение $Dpid удалено", "ok");
		header("Location: {$_SERVER['REQUEST_URI']}");
	}else{
		show_message("Сообщение $Dpid не было удалено: ".$mysqli->error, "error");
		header("Location: {$_SERVER['REQUEST_URI']}");
	}
}
//header
include('header.php');
//show body
$sql = "Select * FROM Messages WHERE Addressee='$login' AND Deleted =0 ORDER BY PID";
$result = $mysqli->query($sql) OR my_die("Ошибка выбора сообщений: ".$mysqli->error);
echo"<table border = 1><div class=caption_table>Ваши сообщения</div><tr><th>Дата</th><th>Отправитель</th><th>Сообщение</th><th colspan='2'></th></tr>";
while($row = $result->fetch_array()){
	$sender = $row['Sender'];
	$message = $row['Message'];
	$pid = $row['PID'];
	$date = $row['Date'];
	$viewed = $row['Viewed'];
	echo"
	<tr>
		<td width=10%>$date</td><td width=7.5%>$sender</td><td>$message</td>
		<td width='7.5%'>
		<form method=POST>
			<input type=text value=$pid name=Dpid hidden>
			<input type=submit value=Удалить>
		</form></td>
		<td width='7.5%'><a href='message_answer.php?pid=$pid'>";
		if($viewed == 1){
			echo"<b>ответить</b>";
		}else{
			echo"ответить";
		}echo"</a></td>
	</tr>";
}
echo"</table>";
include('footer.php');