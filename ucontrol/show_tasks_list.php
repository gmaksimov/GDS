<?php
/*
 * This page is for wathing the progress of tests & which tests is not related to users
 */

/**
 * Prints <td></td> with answers progress of test.
 * 
 * @param int $real_taskcount
 * @param int $taskcount
 * @param int $pid
 * @param int $colspan
 */
function show_answers_progress($real_taskcount, $taskcount, $pid, $colspan = -1){
	
	if($colspan == -1){
		$colspan = "";
	}else{
		$colspan = " colspan = '$colspan'";
	}
	
	//taskcount formatting
	$delta_tc = $taskcount - $real_taskcount;
	$const = 60;
	if($delta_tc >= 0 || $taskcount == 0){
		$width = $const;
	}else{
		$width = $const * $taskcount / $real_taskcount;
		$sec_width = $const * abs($delta_tc) / $real_taskcount;
	}
	
	echo "<td$colspan>
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
}

//start right column
echo "<div style = 'display:inline-table;'>";

//select exam
$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY Year DESC, Halfyear DESC, Paper DESC";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);

/** @var integer $c Is to know is it the first test*/
$c = 1;

$pr_date = array(-1, -1, -1);

/** @var integer $exam Contains PID of 'subject', that belongs to test, what you are viewing */
if(isset($_GET['exam'])){
	$exam = addslashes($_GET['exam']);
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

	$date = array($year, $halfyear, $paper);

	//if different 'subjects', but same test, $date == $pr_date
	if($date !== $pr_date){

		//you are don't have privs for any test, let's select the first one
		if($exam == NULL && $c == 1){
			echo"<option value=$pid selected>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
			$exam = $pid;
		}else{
			//you have privs for specifyed test, let's select it for you
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

//Show 'subjects' by 'booktets'
$date = array("-1", "-1");
$table_started = false;

//vars for 'checked' privs
$booklet_answers = 0;
$booklet_max_answers = 0;

$grade_answers = 0;
$grade_max_answers = 0;

$subject_answers = 0;
$subject_max_answers = 0;

$test_answers = 0;
$test_max_answers = 0;

while($row = $result->fetch_array()){

	//get vars of 'subject'
	$pid = $row['PID'];
	$subject = $row['Subject'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$position = $row['Position'];
	$taskcount = $row['Taskcount'];
	
	//get real_taskcount
	$sql = "SELECT * FROM Tasks WHERE Tpid=$pid";
	$result1 = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$real_taskcount = $result1->num_rows;

	$cur_date = array($grade, $booklet);

	//if different 'subjects', but same test, $cur_date == $date
	if($cur_date[0] != $date[0]){

		//end of grade
		if($table_started){
			echo "
			<tr>
				<th>∑:</th>";
			show_answers_progress($booklet_answers, $booklet_max_answers, $pid, 2);
			$grade_answers += $booklet_answers;
			$grade_max_answers += $booklet_max_answers;
			$booklet_answers = 0;
			$booklet_max_answers = 0;
			echo "
				</table>
			</div>
			<div style='display: inline-table;'>
				<table border=1 width='70%' style='white-space: nowrap;'>
					<tr>
						<th>Total:</th>
					</tr>";
			echo "<tr>";
			show_answers_progress($grade_answers, $grade_max_answers, $pid);
			$test_answers += $grade_answers;
			$test_max_answers += $grade_max_answers;
			$grade_max_answers = 0;
			$grade_answers = 0;
			echo "</tr></table></div></div>";
		}
		$date = $cur_date;

		//start of grade
		echo "
	<!--grade-->
	<div>
		<div class=caption_table>Класс: $grade</div>
		<!--booklet-->
		<div style='display: inline-table;'>
			<table border=1 width='70%' style='white-space: nowrap;'>
				<tr>
					<th>№</th>
					<th>$booklet</th>
					<th>progress</th>
				</tr>";

		$table_started = true;
	}else if($cur_date[1] != $date[1]){

		//end of booklet
		if($table_started){
			echo "
			<tr>
				<th>∑:</th>";
			show_answers_progress($booklet_answers, $booklet_max_answers, $pid, 2);
			$grade_answers += $booklet_answers;
			$grade_max_answers += $booklet_max_answers;
			$booklet_answers = 0;
			$booklet_max_answers = 0;
			echo "</table></div>";
		}

		//start of booklet
		$date = $cur_date;
		echo "
		<!--booklet-->
		<div style='display: inline-table;'>
			<table border=1 width='70%' style='white-space: nowrap;'>
				<tr>
					<th>№</th>
					<th>$booklet</th>
					<th>progress</th>";
	}

	
	
	//increase taskcounts
	$booklet_answers += $real_taskcount;
	$booklet_max_answers += $taskcount;  

	//print subject
	echo"
	<tr id='tr$pid'>
		<td><center><b>".($position / 10)."</b></center></td>
		<td>$subject</td>";
	show_answers_progress($real_taskcount, $taskcount, $pid);
	echo "
	</tr>";
}

//end of last grade
if($table_started){
	echo "
	<tr>
		<th>∑:</th>";
	show_answers_progress($booklet_answers, $booklet_max_answers, $pid, 2);
	echo "
		</tr>
	</table>
	</div>
	<div style='display: inline-table;'>
		<table border=1 width='70%' style='white-space: nowrap;'>
			<tr>
				<th>Total:</th>
			</tr>";
	echo "<tr>";
	show_answers_progress($grade_answers, $grade_max_answers, $pid);
	echo "</tr></table></div></div>";
}

//end right column
echo "
</div>";