<?php
require('header_req.php');
$NO_TINYMCE = 1;
if(!isset($_GET['pid'])){
	my_die("Не дан PID");
}

$tpid = $_GET['pid'];

if(!check_privilegies($tpid)){
	my_die("У вас нет права доступа к этому заданию, нужно $tpid");
}

if(isset($_POST['tasks'])){
	$cols = addslashes($_POST['tasks']);
	for($i = 1; $i <= $cols; $i++){
		$answers[$i] = addslashes($_POST["ans$i"]);
	}
	if(!isset($answers)){
		my_die("No answers, error");
	}
	$sql = "SELECT * FROM Tasks WHERE Tpid=$tpid ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения Tpid'а: ".$mysqli->error);
	for($i = 1; $i <= $cols; $i++){
		$row = $result->fetch_array();
		$pid = $row['PID'];
		$sql = "UPDATE Tasks SET Answer='{$answers[$i]}' WHERE PID=$pid";
		if(!$mysqli->query($sql)){
			my_die("Ошибка сохранения ответов: ".$mysqli->error);
		}
	}
	header("Location: {$_SERVER['REQUEST_URI']}");
}

include('header.php');
$sql = "SELECT * FROM Tasks WHERE Tpid=$tpid ORDER BY Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$tasks = $result->num_rows;
echo"Есть $tasks заданий(и ответов).<br>";
echo"<form method=POST>";
for($i = 1; $row = $result->fetch_array(); $i++){
	$answer = $row['Answer'];
	if($i % 10 == 0){
		echo "<div style='width: 20px; display: inline-block; text-align: center'><font color='red'> $i</font>: </div>";
	}else{
		echo "<div style='width: 20px; display: inline-block; text-align: center'>$i: </div>";
	}
	echo select_numbers("ans$i", 1, 4, $answer);
	//echo"<select type=text id=ans$i name=ans$i style='width: 20px;' value=$answer><br>";
}
echo"<br>
<input type=text name=tasks hidden value=$tasks>
<input type=submit value='Сохранить [s]' accesskey='s'>
</form>";

include('footer.php');

?>