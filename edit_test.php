<?php
require('header_req.php');
include ('header.php');

if(isset($_POST['paper']) && isset($_POST['year']) 
   && isset($_POST['halfyear']) && isset($_POST['grade']) 
   && isset($_POST['booklet']) && isset($_POST['subject'])
   && isset($_POST['position']) && isset($_POST['pid'])){
	$paper = addslashes($_POST['paper']);
	$year = addslashes($_POST['year']);
	$halfyear = addslashes($_POST['halfyear']);
	$grade = addslashes($_POST['grade']);
	$booklet = addslashes($_POST['booklet']);
	$subject = addslashes($_POST['subject']);
	$position = addslashes($_POST['position']);
	$pid = addslashes($_POST['pid']);
	if(isset($_POST['taskcount']) && $_POST['taskcount'] > 0){
		$taskcount = addslashes($_POST['taskcount']);
	}else{
		$taskcount = 0;
	}
	
	//create new test
	if($pid == -1){
		if(!check_privilegies("-1")){
			my_die("У вас нет права на создание предмета, нужно -1");
		}
		$sql = "SELECT Position FROM Tests WHERE
		Paper = '$paper' AND Year = '$year' AND	Halfyear = '$halfyear'
		AND Grade = '$grade' AND Booklet = '$booklet' ORDER BY Position DESC";
		$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
		$row = $result->fetch_array();
		$position = $row['Position'] + 1;
		$sql = "INSERT INTO Tests (Paper, Year, Halfyear, Grade, Booklet, Subject, Position, Taskcount) VALUES
		('$paper', '$year', '$halfyear', '$grade', '$booklet', '$subject', '$position', '$taskcount')";
		$result = $mysqli->query($sql) OR my_die("Ошибка создания теста: ".$mysqli->error);
		$pid = $mysqli->insert_id;
		refresh_test_positions();
		header("Location: test_list.php?exam=$pid");
	}
	
	//update test
	if(!check_privilegies($pid)){
		my_die("У вас нет права на изменение предмета, нужно $pid");
	}
	$sql = "UPDATE Tests SET 
	Paper = '$paper', Year = '$year', Halfyear = '$halfyear',
	Grade = '$grade', Booklet = '$booklet', Subject = '$subject',
	Position = '$position', Taskcount = '$taskcount' WHERE PID = '$pid'";
	if(!$mysqli->query($sql)){
		my_die("Ошибка сохранения теста: ".$mysqli->error);
	}
	refresh_test_positions();
	header("Location: test_list.php?exam=$pid");
}

//create new test
if(isset($_GET['new']) && $_GET['new'] == 1){
	$paper = "";
	$year = "";
	$halfyear = "";
	$grade = "";
	$booklet = "";
	$subject = "";
	$time = 0;
	$position = -1;
	$pid = -1;
	$taskcount = 0;
}else if(isset($_GET['exam']) && $_GET['exam'] != NULL){	//fast adding
	if(!check_privilegies("-1")){
		my_die("У вас нет права на создание предмета, нужно -1");
	}
	$exam = addslashes($_GET['exam']);
	echo"Добавление/редактирование теста:<br>";
	$sql = "SELECT * FROM Tests WHERE Deleted=0 AND PID = $exam";
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$row = $result->fetch_array();
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$paper = $row['Paper'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$taskcount = 0;
	$subject = "";
	$time = 0;
	$position = -1;
	$pid = -1;
}else if(isset($_GET['pid']) && $_GET['pid'] != NULL){
	$pid = addslashes($_GET['pid']);
	$sql = "SELECT * FROM Tests WHERE Deleted=0 AND PID = $pid";
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$row = $result->fetch_array();
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$paper = $row['Paper'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$subject = $row['Subject'];
	$time = $row['Time'];
	$position = $row['Position'];
	$taskcount = $row['Taskcount'];
}else{
	my_die("Не дан PID");
}

//SELECTING DATA
echo "<form method=POST>";

if(isset($_GET['new']) && $_GET['new'] == 1){
	//SELECT Paper
	echo input_data_list("Название теста","paper", "Paper", $paper);
	//SELECT Year
	echo input_data_list("Год","year", "Year", $year);
	//SELECT Halfyear
	echo input_data_list("Полугодие","halfyear", "Halfyear", $halfyear);
	//SELECT Grade
	echo input_data_list("Класс","grade", "Grade", $grade);
	//SELECT Booklet
	echo input_data_list("Буклет","booklet", "Booklet", $booklet);
	//SELECT Subject
	echo input_data_list("Предмет","subject", "Subject", $subject);
}else{
	//SELECT Paper
	echo select_data("Название теста","paper", "Paper", $paper);
	//SELECT Year
	echo select_data("Год","year", "Year", $year);
	//SELECT Halfyear
	echo select_data("Полугодие","halfyear", "Halfyear", $halfyear);
	//SELECT Grade
	echo select_data("Класс","grade", "Grade", $grade);
	//SELECT Booklet
	echo select_data("Буклет","booklet", "Booklet", $booklet);
	//SELECT Subject
	echo input_data_list("Предмет","subject", "Subject", $subject);
}
echo "
	<label for=taskcount>Кол-во вопросов: </label><input type=number min=0 value='$taskcount' name=taskcount id=taskcount><br>
	<input type=text hidden value='$position' name=position>
	<input type=text hidden value='$pid' name=pid>
	<input type=submit value='Сохранить [s]' title='accesskey: [s]' accesskey=s>
</form>";
include('footer.php');
?>
