<?php
/**
 * This page is for editing 'subjects'' properties.
 */

require('header_req.php');

if(!isset($_GET['pid'])){
	my_die("Ошибка: Не дан PID");
}

//check privilegies
if(!check_privilegies("-1")){
	my_die("У вас нет прав для просмотра этой странички, нужно -1");
}

//get vars
$pid = addslashes($_GET['pid']);
$sql = "SELECT * FROM Tests WHERE PID='$pid'";
$tests = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$row = $tests->fetch_array();
$year = $row['Year'];
$paper = $row['Paper'];
$halfyear = $row['Halfyear'];
$grade = $row['Grade'];
$booklet = $row['Booklet'];
$subject = $row['Subject'];

//What subjects will be updated
if(isset($_POST['subjects']) && count($_POST['subjects']) != 0){
	for($i = 0; $i < count($_POST['subjects']); $i++){
		$sql = "SELECT * FROM Tests WHERE PID=".addslashes($_POST['subjects'][$i])." ORDER BY Position";
		$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
		$subjects[$i] = $result->fetch_array();
	}
}else{
	if(isset($_GET['type']) && $_GET['type'] == "subject"){
		$sql = "SELECT * FROM Tests WHERE Paper='$paper' AND Year='$year' AND Halfyear='$halfyear' AND Subject='$subject' AND Deleted=0 ORDER BY Grade, Booklet, Position";
		$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
		for($i = 0; $row = $result->fetch_array(); $i++){
			$subjects[$i] = $row;
		}
	}else{
		$sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Paper='$paper' AND Deleted=0 ORDER BY Position";
		$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
		for($i = 0; $row = $result->fetch_array(); $i++){
			$subjects[$i] = $row;
		}
	}
}

//set time in the selected tests
if(isset($_POST['time']) && $_POST['time'] == 1 && isset($_POST['variable'])){
	$variable = $_POST['variable'];
	for($i = 0; $i < count($subjects); $i++){
		$row = $subjects[$i];
		$tpid = $row['PID'];

		//get amount of tasks
		$sql = "SELECT * FROM Tasks WHERE Tpid='$tpid'";
		$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
		$time = $result->num_rows * $variable;

		//update time of 'subject'
		$sql = "UPDATE Tests SET Time=$time WHERE PID = $tpid";
		if(!$mysqli->query($sql)){
			my_die("Ошибка изменения времени теста $tpid: ".$mysqli->error);
		}
	}
}

//copy subjects to another booklet
if(isset($_POST['copy_tests']) && $_POST['copy_tests'] == 1 && isset($_POST['booklet']) && isset($_POST['grade']) && isset($_POST['subject'])){

	for($i = 0; $i < count($subjects); $i++){
		$row = $subjects[$i];
		$test_pid = $row['PID'];
		$taskcount = $row['Taskcount'];

		//for correct subjects/booklets work
		if(isset($_GET['type']) && $_GET['type'] == "subject"){
			$mysubject = addslashes($_POST['subject']);
			$mybooklet = $row['Booklet'];
			$mygrade = $row['Grade'];
		}else{
			$mybooklet = addslashes($_POST['booklet']);
			$mygrade = addslashes($_POST['grade']);
			$mysubject = $row['Subject'];
		}

		//insert new 'subject'
		$sql = "INSERT INTO Tests (Year, Halfyear, Grade, Paper, Booklet, Subject, Position, Taskcount) VALUES ($year, $halfyear, $mygrade, '$paper', '$mybooklet', '$mysubject', 10050000000, '$taskcount')";
		$result3 = $mysqli->query($sql) OR my_die("Ошибка копирования предметов в буклет B: ".$mysqli->error);
		$last_id = $mysqli->insert_id;

		//copy tasks too
		if(isset($_POST['tasks']) && $_POST['tasks'] == 1){

			//randomise tasks position
			if(isset($_POST['rand']) && $_POST['rand'] == 1){
				$rd = "rand()";
			}else{
				$rd = "Position";
			}

			$sql = "SELECT * FROM Tasks WHERE Tpid=$test_pid ORDER BY $rd";
			$result1 = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);

			for($pos = 1; $test = $result1->fetch_array(); $pos++){
				$sql = "INSERT INTO Tasks (Question, Ans1, Ans2, Ans3, Ans4, Answer, Tpid, Position, Picture) VALUES ('{$test['Question']}', '{$test['Ans1']}', '{$test['Ans2']}', '{$test['Ans3']}', '{$test['Ans4']}', '{$test['Answer']}', $last_id, $pos, '{$test['Picture']}')";
				if(!$mysqli->query($sql)){
					my_die("Ошибка копирования заданий из $test_pid ".$mysqli->error);
				}
			}
			refresh_task_positions($last_id);
		}
	}
	refresh_test_positions();
}

//randomise tasks without copying
if(isset($_POST['rand_cur_tasks']) && $_POST['rand_cur_tasks'] != NULL){
	for($i = 0; $i < count($subjects); $i++){
		$row = $subjects[$i];
		$rd_pid = $row['PID'];

		$sql = "SELECT * FROM Tasks WHERE Tpid=$rd_pid ORDER BY rand()";
		$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);

		for($new_pos = 0; $row = $result->fetch_array(); $new_pos++){
			$task_pid = $row['PID'];
			$sql = "UPDATE Tasks SET Position =$new_pos WHERE PID=$task_pid";
			if(!$mysqli->query($sql)){
				my_die("Error repositioning tasks, id $rd_pid: ".$mysqli->error);
			}
		}
		refresh_task_positions($rd_pid);
	}
}

//set taskcount in the selected tests
if(isset($_POST['taskcount']) && $_POST['taskcount'] == 1 && isset($_POST['taskcount_num'])){
	$taskcount_num = addslashes($_POST['taskcount_num']);
	for($i = 0; $i < count($subjects); $i++){
		$row = $subjects[$i];
		$tpid = $row['PID'];

		//get real taskcount of test
		if(isset($_POST['set_real_taskcount']) && $_POST['set_real_taskcount'] == 1){
			$sql = "SELECT * FROM Tasks WHERE Tpid=$pid";
			$result1 = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
			$taskcount_num = $result1->num_rows;
		}

		//update taskcount of 'subject'
		$sql = "UPDATE Tests SET Taskcount=$taskcount_num WHERE PID = $tpid";
		if(!$mysqli->query($sql)){
			my_die("Ошибка изменения количества заданий теста $tpid: ".$mysqli->error);
		}
	}
}

//Change selected sujects' data
if(isset($_POST['change_test']) && $_POST['change_test'] == 1 && isset($_POST['new_paper_name']) && isset($_POST['new_year_name']) && isset($_POST['new_halfyear_name']) && isset($_POST['new_booklet_name']) && isset($_POST['new_grade_name']) && isset($_POST['new_subject_name'])){
	$new_paper_name = addslashes($_POST['new_paper_name']);
	$new_year_name = addslashes($_POST['new_year_name']);
	$new_halfyear_name = addslashes($_POST['new_halfyear_name']);
	$new_booklet_name = addslashes($_POST['new_booklet_name']);
	$new_grade_name = addslashes($_POST['new_grade_name']);
	$new_subject_name = addslashes($_POST['new_subject_name']);

	//for correct subjects/booklets work
	if(isset($_GET['type']) && $_GET['type'] == "subject"){
		$new_subject_name = ", Subject='$new_subject_name'";
		$new_grade_name = "";
		$new_booklet_name = "";
	}else{
		$new_booklet_name = ", Booklet = '$new_booklet_name'";
		$new_grade_name = ", Grade = '$new_grade_name'";
		$new_subject_name = "";
	}
	for($i = 0; $i < count($subjects); $i++){
		$row = $subjects[$i];
		$tpid = $row['PID'];

		//update subject name
		$sql = "UPDATE Tests SET Paper='$new_paper_name', Year = '$new_year_name', Halfyear = '$new_halfyear_name'$new_grade_name$new_booklet_name$new_subject_name WHERE PID = $tpid";
		if(!$mysqli->query($sql)){
			my_die("Ошибка изменения имени теста $tpid: ".$mysqli->error);
		}
	}
}

//clear $_POST
if(isset($_POST['time']) || isset($_POST['copy_tests']) || isset($_POST['rand_cur_tasks']) || isset($_POST['taskcount']) || isset($_POST['change_test'])){
	header("Location: {$_SERVER['REQUEST_URI']}");
}

include ('header.php');


echo"
<h2>Опции:</h2>";

//for subject group work
if(isset($_GET['type']) && $_GET['type'] == "subject"){
	echo"
<h2>Предмет: $subject</h2>
<font size=3>Выберите буклеты:</font>
<br>
<form method=POST>
<select name='subjects[]' multiple size=5>";

	//print multi-select list
	$sql = "SELECT * FROM Tests WHERE Paper='$paper' AND Year='$year' AND Halfyear='$halfyear' AND Subject='$subject' AND Deleted=0 ORDER BY Grade, Booklet, Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	while($row = $result->fetch_array()){
		$gr = $row['Grade'];
		$bk = $row['Booklet'];
		$tp = $row['PID'];
		echo "<option value='$tp'>Grade: $gr, Booklet: $bk</option>";
	}

	//for booklet group work
}else{
	echo"
<h2>Класс: $grade, Буклет: $booklet</h2>
<font size=3>Выберите предметы:</font>
<br>
<form method=POST>
<select name='subjects[]' multiple size=5>";

	//print multi-select list
	$sql = "SELECT * FROM Tests WHERE Paper='$paper' AND Year='$year' AND Halfyear='$halfyear' AND Grade='$grade' AND Booklet='$booklet' AND Deleted=0 ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	while($row = $result->fetch_array()){
		$sb = $row['Subject'];
		$tp = $row['PID'];
		echo "<option value='$tp'>$sb</option>";
	}
}
?>
</select>
<br>

<font size=3>Выберите действие(я):</font>
<br>

<input type=checkbox
	name=change_test value=1>
Изменить аттребуты:
<ul style='margin: 1px'>
	<li><?php echo input_data_list("Название теста","new_paper_name", "Paper", $paper)?>
	</li>
	<li><?php echo input_data_list("Год","new_year_name", "Year", $year)?>
	</li>
	<li><?php echo input_data_list("Полугодие","new_halfyear_name", "Halfyear", $halfyear)?>
	</li>
	<?php
	//for correct subjects/booklets work
	if(isset($_GET['type']) && $_GET['type'] == "subject"){
		$hide_subject = "";
		$hide_bkandgrade = "hidden";
	}else{
		$hide_subject = "hidden";
		$hide_bkandgrade = "";
	}
	?>
	<li <?php echo $hide_subject?>><?php echo input_data_list("Предмет","new_subject_name", "Subject", $subject)?>
	</li>
	<li <?php echo $hide_bkandgrade?>><?php echo input_data_list("Класс","new_grade_name", "Grade", $grade)?>
	</li>
	<li <?php echo $hide_bkandgrade?>><?php echo input_data_list("Буклет","new_booklet_name", "Booklet", $booklet)?>
	</li>
</ul>
<br>

<input type=checkbox name=time value=1>
Время: количество заданий *
<input type=text name=variable
	placeholder='Число' value='' style='width: 40px' pattern='^[ 0-9]+$'>
<br>

<input type=checkbox name=rand_cur_tasks value=1>
Перемешать задания в текущем буклете
<br>

<input type=checkbox
	name=taskcount value=1>
Изменить количество заданий:
<ul style='margin: 1px'>
	<li><input type=radio name=set_real_taskcount value=1>Количество
		заданий = реальному кол-ву заданий.</li>
	<li><input type=radio name=set_real_taskcount value=0>Указать
		количество заданий: <input type=text name=taskcount_num
		placeholder='Число' style='width: 40px' pattern='^[ 0-9]+$'>
	</li>
</ul>
<br>

<input type=checkbox name=copy_tests value=1>
Копирование:
<ul style='margin: 1px'>
	<li <?php echo $hide_bkandgrade?>>Буклет: <input type=text name=booklet
		style='width: 60px' placeholder='№/буква'>
	</li>
	<li <?php echo $hide_bkandgrade?>>Класс: <input type=text name=grade
		style='width: 60px' placeholder='№'>
	</li>
	<li <?php echo $hide_subject?>>Класс: <input type=text name=subject
		placeholder='Название предмета'>
	</li>
	<ul style='margin: 1px'>
		<li>(<span title='Иначе в скопированном тесте не будет заданий'>копировать
				задания</span>)<input type=checkbox name=tasks value=1>
		</li>
		<li>(перемешать задания в скопированном буклете)<input type=checkbox
			name=rand value=1>
		</li>
	</ul>
</ul>
<br>
<input type=submit
	value='Сохранить[s]' accesskey=s>
</form>

	<?php

	include('footer.php');