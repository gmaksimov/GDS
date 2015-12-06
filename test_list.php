<?php
/**
 * This page is for displaying tests
 */

require('header_req.php');

//Marks Test for Deletion in db
if(isset($_POST['Dpid'])){
	if(!check_privilegies("-1")){
		my_die("У вас нет права на удаление этого предмета, нужно -1");
	}
	$Dpid = $_POST['Dpid'];
	$sql = "UPDATE Tests SET Deleted=1 WHERE PID='$Dpid'";
	if($mysqli->query($sql)){
		show_message("Тест $Dpid удален", "ok");
		header("Location: {$_SERVER['REQUEST_URI']}");
	} else {
		my_die("Ошибка удаления предмета: ".$mysqli->error, "error");
	}
}

//Creates new Test
if(isset($_POST['Create']) && $_POST['Create'] != NULL){
	if(!check_privilegies("-1")){
		my_die("У вас нет права на создание предмета, нужно -1");
	}
	if(isset($_POST['test_data'])){
		$exam = addslashes($_POST['test_data']);
	}

	//create new test or new 'subject' in the test
	if(isset($_POST['new']) && $_POST['new'] != NULL){
		$new = "&new=1";
	}else{
		$new = "";
	}

	header("Location: edit_test.php?exam=$exam$new");
}

//Changing Position. Put after second
if(isset($_POST['first']) && isset($_POST['second'])){
	$first = addslashes($_POST['first']);
	$second = addslashes($_POST['second']);

	$sql = "SELECT Position FROM Tests WHERE PID=$first";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$pos1 = $row['Position'];

	$sql = "SELECT Position FROM Tests WHERE PID=$second";
	$result2 = $mysqli->query($sql) OR my_die("Ошибка получения данных".$mysqli->error);
	$row = $result2->fetch_array();
	$pos2 = $row['Position'];

	//assign new position, +- 5, because position differs in 10
	if($pos1 > $pos2){
		$new_pos = $pos2 - 5;
	}else{
		$new_pos = $pos2 + 5;
	}

	//update only one, second will be in right place
	$sql = "UPDATE Tests SET Position=$new_pos WHERE PID=$first";
	if(!$mysqli->query($sql)){
		my_die("Ошибка обновления позиции предмета".$mysqli->error);
	}

	//update positions (make them differ in 10 again)
	refresh_test_positions();

	header("Location: {$_SERVER['REQUEST_URI']}");
}

include ('header.php');

//select exam
$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY Year DESC, Halfyear DESC, Paper DESC";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);


/** @var integer $first_ex Contains -1 if exam is isset, othervise will contain PID of first test */
$first_ex = -1;

/** @var integer $c Is to know is it the first test*/
$c = 1;

$pr_date = array(-1, -1, -1);

/** @var integer $exam Contains PID of 'subject', that belongs to test, what you are viewing */
if(isset($_GET['exam'])){
	$exam = $_GET['exam'];
}else{
	$exam=NULL;
}

//print drop down list with tests
echo"
<form method=GET>
	<select name=exam onchange='this.form.submit()'>";

while($row = $result->fetch_array()){
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$paper = $row['Paper'];
	$pid = $row['PID'];

	if(!check_privilegies($pid)){
		continue;
	}

	$date = array($year, $halfyear, $paper);

	//if different 'subjects', but same test, $date == $pr_date
	if($date !== $pr_date){

		//you are not watching specifyed test, let's select the first one
		if($exam == NULL && $c == 1){
			echo"<option value=$pid selected>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
			$first_ex = $pid;
		}else{
			//you are watching specifyed test, let's select it for you
			if($exam == $pid){
				echo"<option value=$pid selected>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
			}else{
				echo"<option value=$pid>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
			}
		}
		/* if $exam == NULL
		 *		we selected first test, so none of the others should be selected
		 */
		$c=2;
	}
	$pr_date = $date;
}

echo"
	</select>
</form>
<br>";

//show button to create new test
if(check_privilegies("-1")){
	echo "<form method=POST>
	  <input type=text name=Create value=rly hidden=hidden>
	  <input type=text name=new value=rly hidden>
	  <input type=submit value=\"Создать тест[q]\" accesskey=q>
	</form>";
}

//show exam
//if no exam viewed and no first exam, that you have privilegies to, selected
if(!isset($_GET['exam']) && $first_ex == -1){
	my_die("Пожалуйста, выберите тест");
}else if(!isset($_GET['exam'])){
	$exam = $first_ex;
}

//get vars
$sql = "SELECT * FROM Tests WHERE PID='$exam'";
$result = $mysqli->query($sql);
$row = $result->fetch_array();
$year = $row['Year'];
$halfyear = $row['Halfyear'];
$paper = $row['Paper'];

//select all 'grades' of test
$sql = "SELECT * FROM Tests WHERE Deleted=0 AND Paper ='$paper' AND Halfyear = '$halfyear' AND Year = '$year' ORDER BY Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);

//show table
$table1 = "<table border=1 width='70%'><div class=caption_table>";
$table2 = "</div><tr>";
$colsp = 0;

if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
	$table2 .="<th>PID</th>";
}

$table2 .= "<th>Предмет</th>
		<th>Кол-во заданий</th>";

//create 'subject' button
if(check_privilegies("-1")){
	$table2 .= "
		<th colspan=2>
			<form method=POST>
				<input type=text name=Create value=rly hidden=hidden>
				<input type=text name=test_data value=";
	$table2_end = " hidden>
				<input type=submit value=\"Создать предмет\">
			</form>
		</th>
		<th colspan=3>
		</th>";
}else{
	$table2 .= "<th colspan=2></th>";
	$table2_end= "";
}

//Show 'subjects' by 'booktets'
$date = array();
$table_started = false;
while($row = $result->fetch_array()){

	//get vars of 'subject'
	$subject = $row['Subject'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$pid = $row['PID'];
	$position = $row['Position'];
	$time = $row['Time'];
	$taskcount = $row['Taskcount'];

	//get real_taskcount
	$sql = "SELECT * FROM Tasks WHERE Tpid=$pid";
	$result1 = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$real_taskcount = $result1->num_rows;

	if(!check_privilegies($pid)){
		continue;
	}

	$cur_date = array($grade, $booklet);

	//if different 'subjects', but same test, $cur_date == $date
	if($cur_date != $date){
		if($table_started){
			echo "</table>";
		}
		$date = $cur_date;
		$date_string = "Класс: $grade, Буклет: $booklet";

		//show print (whole 'booklet'), option, answers
		if(check_privilegies("-1")){
			$date_string .= " <a href='$current_print_folder/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper' target = '_blank' class=capt>Печать по-админски</a>
	  <a href='options.php?pid=$pid' class=capt>опции</a>
	  <a href='answers.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper' target = '_blank' class=capt>ответы</a>";
		}

		echo $table1.$date_string.$table2;

		//for create 'subject' button
		if(check_privilegies("-1")){
			echo $pid;
		}

		echo $table2_end;
		$table_started = true;
	}

	echo "<tr style='height: 24px'>";

	if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
		echo"<td width=3%>$pid</td>";
	}

	echo"<td>$subject</td>";

	//taskcount formatting
	$delta_tc = $taskcount - $real_taskcount;
	if($delta_tc >= 0 || $taskcount == 0){
		$width = 70;
	}else{
		$width = 70 * $taskcount / $real_taskcount;
		$sec_width = 70 * abs($delta_tc) / $real_taskcount;
	}
	echo "<td width=10%>
		<meter id='bar$pid' min='0' max='100' low='25' high='75' optimum='100' value='";
	if($taskcount == 0){
		echo "0";
	}else{
		echo $real_taskcount / $taskcount * 100;
	}
	echo "' style='width:".$width."%;'></meter>";
	if($delta_tc < 0 && $taskcount != 0){
		echo "<meter min='0' max='100' low='0' high='0' optimum='0' value='100' style='width:".$sec_width."%;'></meter>";
	}
	echo "<label for='bar$pid'> $real_taskcount/$taskcount</label>";
	echo "</td>";

	//swapping
	if(check_privilegies("-1")){
		echo"
	    <td width=4%>
	    <form method=POST>
	    	<input name=first hidden value=$pid>
			<select name='second' onchange='this.form.submit()'>";

		//'select' for swapping
		select_test_pid($pid);

		echo"
			</select>
		</form>";
	}

	echo"
	<td width='7.5%'>
		<a href='task_list.php?tpid=$pid'>Задания</a></td>";

	//show delete 'subject' button
	if(check_privilegies("-1")){
		echo "
		<td width='7.5%'>
			<form method=POST>
				<input type=text value=$pid name=Dpid hidden> <input onclick=\"return
					confirm('Вы действитель хотите удалить предмет?')\" type=submit
					value=Удалить>
			</form>
		</td>
		<td width='8%'>
			<a href='options.php?pid=$pid&type=subject'>Редактировать</a>
		</td>";
	}

	echo"
	<td width='7.5%'>
		<a href='$current_print_folder/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper&pid=$pid' target = '_blank'>Печатать</a>
	</td>
	</tr>";
}

if($table_started){
	echo "</table>";
}

include('footer.php');
?>
