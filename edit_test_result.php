<?php 
require('header_req.php');
if(isset($_POST['data']) && $_POST['data'] != NULL){
	$pid = addslashes($_POST['data']);
	if(!check_privilegies(-1)){
		my_die("Ошибка быстрого добавления: у вас нет права доступа к изменению этого теста, нужно -1");
	}
	$sql = "SELECT * FROM Tests WHERE PID='$pid'";
	$result = $mysqli->query($sql) OR my_die("Error fast adding: ".$mysqli->error);
	$row = $result->fetch_array();
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$paper = $row['Paper'];
	$subject = addslashes($_POST['subject']);
	$time = addslashes($_POST['time']);
	$pid = addslashes($_POST['tpid']);
}else{
if(!isset($_POST['pid']) && $_POST['pid'] == NULL){
  my_die("Не дан PID");
}
$pid = $_POST['pid'];
if(!check_privilegies(-1)){
  my_die("у вас нет права доступа к изменению этого теста, нужно -1");
}

$subject = addslashes($_POST['subject']);
$year = addslashes($_POST['year']);
$halfyear = addslashes($_POST['halfyear']);
$grade = addslashes($_POST['grade']);
$position = addslashes($_POST['position']);
$booklet = addslashes($_POST['booklet']);
$paper = addslashes($_POST['paper']);
$time = addslashes($_POST['time']);
$pid = $_POST['pid'];
}
if($position != 0){
	$sql = "SELECT * FROM Tests WHERE Deleted = 0 AND Year = '$year' AND Halfyear = '$halfyear' AND Grade = '$grade' AND Booklet = '$booklet' AND Paper = '$paper' ORDER BY Position DESC";
	$result = $mysqli->query($sql) OR my_die("Error checking position: ".$mysqli->error);
	while($row = $result->fetch_array()){
		$pos = $row['Position'];
		$spid = $row['PID'];
		if($spid != $pid && $position == $pos){
			$sql = "SELECT Position FROM Tests WHERE Deleted = 0 AND Year = '$year' AND Halfyear = '$halfyear' AND Grade = '$grade' AND Booklet = '$booklet' AND Paper = '$paper' ORDER BY Position DESC";
			$result = $mysqli->query($sql) OR my_die("Error selecting position, delete test $pid and create new: ".$mysqli->error);
			$position = $result->num_rows + 1;
			break;
		}
	}
}
if($_POST['position'] == 0 || $_POST['position'] == NULL){
	$sql = "SELECT Position FROM Tests WHERE Deleted = 0 AND Year = '$year' AND Halfyear = '$halfyear' AND Grade = '$grade' AND Booklet = '$booklet' AND Paper = '$paper' ORDER BY Position DESC";
	$result = $mysqli->query($sql) OR my_die("Error selecting position, delete test $pid and create new: ".$mysqli->error);
	$position = $result->num_rows + 1;
}

$position = addslashes($position);

$sql = "UPDATE Tests SET
Subject = '$subject',
Year = '$year',
Halfyear = '$halfyear',
Grade = '$grade',
Booklet = '$booklet',
Position = '$position',
Paper = '$paper',
Time = '$time' 
WHERE PID = '$pid'";

if($mysqli->query($sql)){
  //show_message("OK, к <a href=test_list.php>тестам</a>", "ok");
  header("Location: test_list.php?exam=$pid");
} else {
  show_message("Ошибка сохранения теста: ".$mysqli->error, "error");
}

refresh_test_positions(); //functions.php

include('footer.php');
?>
