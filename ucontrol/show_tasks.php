<?php
/**
 * This page prints table with interface for choosing privs for users
 */

/**
 * Shows link with special attributes.
 *
 * Link has:
 * 1)Class (color) it is red when someone has all privs blue otherwise
 * 2)Unique id
 * 3)Onclick with function multipriv from ucontrol/script.js
 * 4)Inner HTML with $preid
 *
 * @param array $privs
 * @param string $preid
 * @param array $has_privs
 */
function show_priv_field($privs, $preid, $has_privs){
	$str = "";
	$id = $privs[0];
	foreach($privs as $priv){
		$str .= ", $priv";
	}
	if($has_privs){
		$color="red";
		$task = "delete";
	}else{
		$color="blue";
		$task = "add";
	}
	echo"<a href=# class='".$color."taskshow' onclick=\"multipriv('$preid$id', '$task'$str)\" id='$preid$id'><center>$preid</center></a>";
}

/**
 * For lines group work.
 *
 * Calls show_priv_field for each line(same subjects) of test
 * And print all it table manner
 *
 * @param array $subject_privs
 * @param array $has_rows_privs
 */
function show_row_priv($rows_privs, $has_rows_privs){
	$i = 0;
	foreach($rows_privs as $privs){
		echo "<tr><td>";
		show_priv_field($privs, "Subjects", $has_rows_privs[$i]);
		echo "</td></tr>";
		$i++;
	}
}

//get privs
$upid = addslashes($_GET['pid']);
$sql = "SELECT Privilegies From Users WHERE PID=$upid";
$result = $mysqli->query($sql) OR my_die("Error getting privs".$mysqli->error);
$row = $result->fetch_array();
$privs = $row['Privilegies'];

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
	//select test where you have privs
	$privse = explode(' ', $privs);
	if(isset($privse[0]) && $privse[0] != "-1"){
		$exam = $privse[0];
	}else{
		$exam=NULL;
	}

}

//print drop down list with tests
echo"
<form method=GET>
	<input type=text name=pid value=$upid hidden>
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
$booklet_privs = array();
$has_all_booklet_privs = true;

$grade_privs = array();
$has_all_grade_privs = true;

$subject_privs = array(array());
$has_all_subject_privs = array(true);

$test_privs = array();
$has_all_test_privs = true;
$row_num = 0;

while($row = $result->fetch_array()){

	//get vars of 'subject'
	$pid = $row['PID'];
	$subject = $row['Subject'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$position = $row['Position'];

	$cur_date = array($grade, $booklet);

	//if different 'subjects', but same test, $cur_date == $date
	if($cur_date[0] != $date[0]){

		//end of grade
		if($table_started){
			echo "
			<tr>
				<th>Add</th>
				<td>";
			show_priv_field($booklet_privs, "Booklet", $has_all_booklet_privs);
			$booklet_privs = array();
			$has_all_booklet_privs = true;
			echo "
				</td>
				</table>
			</div>
			<div style='display: inline-table;'>
				<table border=1 width='70%' style='white-space: nowrap;'>
					<tr>
						<th>Add</th>
					</tr>";
			show_row_priv($subject_privs, $has_all_subject_privs);
			$subject_privs = array(array());
			$has_all_subject_privs = array(true);
			$row_num = 0;
			echo "<tr><td>";
			show_priv_field($grade_privs, "Grade", $has_all_grade_privs);
			$has_all_grade_privs = true;
			$grade_privs = array();
			echo "</td></tr></table></div></div>";
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
					<th>PID</th>
					<th>subject</th>
				</tr>";

		$table_started = true;
	}else if($cur_date[1] != $date[1]){

		//end of booklet
		if($table_started){
			echo "
			<tr>
				<th>Add</th>
				<td>";
			show_priv_field($booklet_privs, "Booklet", $has_all_booklet_privs);
			$booklet_privs = array();
			$has_all_booklet_privs = true;
			$row_num = 0;
			echo "</td></table></div>";
		}

		//start of booklet
		$date = $cur_date;
		echo "
		<!--booklet-->
		<div style='display: inline-table;'>
			<table border=1 width='70%' style='white-space: nowrap;'>
				<tr>
					<th>PID</th>
					<th>subject</th>";
	}

	//checked if user have privs for the 'subject'
	$red = 0;
	$privse = explode(' ', $privs);
	foreach($privse as $priv){
		if($priv == $pid){
			$red = 1;
			break;
		}
	}

	//set vars for 'checked' privs
	array_push($test_privs, $pid);
	array_push($booklet_privs, $pid);
	array_push($grade_privs, $pid);
	if(!isset($subject_privs[$row_num])){
		array_push($subject_privs, array());
		array_push($has_all_subject_privs, true);
	}
	array_push($subject_privs[$row_num], $pid);
	if($red != 1){
		$has_all_test_privs = false;
		$has_all_grade_privs = false;
		$has_all_booklet_privs = false;
		$has_all_subject_privs[$row_num] = false;

	}
	$row_num++;

	//choose style for subject on the basis of priv: 'checked' it or not
	if($red == 1){
		$href = "<a href=# class='redtaskshow' onclick='deletepriv($pid, ".$booklet_privs[0].", ".$subject_privs[$row_num - 1][0].", ".$grade_privs[0].")' id=$pid>$pid</a>";
		$class = "red";
	}else{
		$href = "<a href=# class='bluetaskshow' onclick='addpriv($pid, ".$booklet_privs[0].", ".$subject_privs[$row_num - 1][0].", ".$grade_privs[0].")' id=$pid>$pid</a>";
		$class = "";
	}

	//print subject
	echo"
	<tr id='tr$pid' class='taskrow$class'>
		<td><b>$href</b></td>
		<td>$subject</td>
	</tr>";
}

//end of last grade
if($table_started){
	echo "
	<tr>
		<th>Add</th>
		<td>";
	show_priv_field($booklet_privs, "Booklet", $has_all_booklet_privs);
	echo "
		</td>
		</tr>
	</table>
	</div>
	<div style='display: inline-table;'>
		<table border=1 width='70%' style='white-space: nowrap;'>
			<tr>
				<th>Add</th>
			</tr>";
	show_row_priv($subject_privs, $has_all_subject_privs);
	echo "<tr><td>";
	show_priv_field($grade_privs, "Grade", $has_all_grade_privs);
	echo "</td></tr></table></div></div>";
}

//end right column
echo "
</div>";

//Third column (choose all button)
echo "
<div style='display: inline-table;'>
	<table border=1 width='70%' style='white-space: nowrap;'>
		<tr>
			<th>Add all/Rm all</th>
		</tr>
		<td>";
show_priv_field($test_privs, "All", $has_all_test_privs);
echo"
		</td>
</div>";
