<?php
require('header_req.php');
$NO_TINYMCE = 1;
include('header.php');
if($login != 'admin'){
	my_die("Error: you have nor enough privacy");
}
if(isset($_POST['sql']) && isset($_POST['num'])){
	$sql = $_POST['sql'];
	$num = $_POST['num'];
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	echo"[OK]";
	while($row = $result->fetch_array()){
		for($i = 0; $i < $num; $i++){
			echo"$row[$i], ";
		}
		echo";<br>";
	}
}
?>
<br><br>Console:
<form method=POST>
<textarea name=sql required></textarea><br>
<input type=text name=num>
<input type=submit value=submit>
</form>