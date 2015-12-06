<?php
/*
 * This page is for editting users' fields
 */

require('header_req.php');
require('header.php');

//Need pid to know what user is editting now
if(!isset($_GET['pid'])){
	my_die("No PID");
}

//get vars
$pid = addslashes($_GET['pid']);

$sql = "SELECT * FROM Users WHERE PID='$pid'";
$result = $mysqli->query($sql) OR my_die($mysqli->error);
$row = $result->fetch_array();

$login = $row['Login'];
$password = $row['Pass'];
$privilegies = $row['Privilegies'];
$pid = $row['PID'];
$mail = $row['Mail'];

//print form
?>

<div style="display: inline-table; width: 400px">
	<form method=POST action=user_list.php?pid= <?php echo $pid; ?>>
		Логин:<br> <input type=text value='<?php echo $login; ?>' name=login><br><br>
		Пароль:<br> <input type=text value='<?php echo $password; ?>' name=password><br>
		Mail:<br> <input type=text value='<?php echo $mail; ?>' name=mail><br><br>
		PID тестов на которые есть права. -1 дает права на все. Про разделители читайте ниже<br>
		<input type=text value='<?php echo $privilegies; ?>' name=privilegies id=privs><br>
		<input type=text hidden=hidden value='<?php echo $pid; ?>' name=pid>
		<input type=submit value=submit><input type=reset title='To default' value=reset>
	</form>
	<br>
	<b>Про права:</b><br>
	Массив прав восстанавливается применением
	preg_split("/[\s,]+/", $privilegies). Это значит, что разделителем
	могут быть <i>пробелы</i> и некоторые другие разделители.<br>
</div>

<?php

//print table to simply select privs
include('show_tasks.php');

include('footer.php');
?>