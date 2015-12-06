<?php
/*
 * This page is for deleting/recovering deleted 'subjects'
 */

require('header_req.php');
include('header.php');

//Deletes Test with its Tasks and pictures
if(isset($_POST['Dpid'])){

	//get pid
	$Dpid = $_POST['Dpid'];

	//delete picture if it there's
	$sql = "SELECT * FROM Tasks WHERE tPID = '$Dpid'";
	$result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
	while($row = $result->fetch_array()){

		//get vars
		$picture = $row['Picture'];
		$taskpid = $row['PID'];

		//delete picture if exists
		if(!$picture == ""){
			if(!unlink("../$picture")){
				show_message("Picture didn't deleted; task id = $taskpid: ".$mysqli->error, "error");
			}
		}
	}

	//delete tasks
	$sql = "Delete FROM Tasks WHERE Tpid='$Dpid'";
	if(!$mysqli->query($sql)){
		show_message("Error deleting tasks from test $Dpid: ".$mysqli->error, "error");
	}

	//delete test
	$sql = "Delete FROM Tests WHERE PID='$Dpid'";
	if($mysqli->query($sql)){
		show_message("Test $Dpid deleted", "ok");
	}else{
		show_message("Error deleting test $Dpid: ".$mysqli->error, "error");
	}

	header("Location: {$_SERVER['REQUEST_URI']}");
}

//recovery of 'subject'
if(isset($_POST['Rpid'])){

	//get pid
	$Rpid = $_POST['Rpid'];

	//set Deleted=0
	$sql = "UPDATE Tests SET Deleted=0 WHERE PID='$Rpid'";
	if($mysqli->query($sql)){
		show_message("Test $Rpid recovered", "ok");
	}else{
		show_message("Error, Test $Rpid did't recovered: ".$mysqli->error, "error");
	}

	header("Location: {$_SERVER['REQUEST_URI']}");
}

//pop up warning
show_message("If you delete \'subject\' from here, you could not get it anymore!","error", "Warning!", -1, true);

//Shows table
$sql = "SELECT * FROM Tests WHERE Deleted=1  ORDER BY Paper, Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
$table1 = "
<table border=1 width='40%'>
	<div class='caption_table'>";
$table2 = "
	</div>
	<tr>
		<th>PID</th>
		<th>Предмет</th>
		<th colspan=3></th>
	</tr>";
//PID  Year   Halfyear   Subject   Grade   Booklet   PID
$date = array();
$table_started = false;
while($row = $result->fetch_array()){
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$subject = $row['Subject'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$pid = $row['PID'];
	$position = $row['Position'];
	$paper = $row['Paper'];

	$cur_date = array($year, $halfyear, $grade, $booklet, $paper);
	if($cur_date != $date){
		if($table_started){
			echo "</table>";
		}
		$date = $cur_date;
		$date_string = "Тест: $paper, Год: $year, Полугодие: $halfyear, Класс: $grade, Буклет: $booklet";
		echo $table1.$date_string.$table2;
		$table_started = true;
	}
	echo "
	<tr>
		<td width=2.5%>$pid</td>
		<td>$subject</td>
		<td width='10%'><form method=POST>
				<input type=text value=$pid name=Dpid hidden> <input type=submit
					value=Удалить>
			</form></td>
		<td width='10%'><form method=POST>
				<input type=text value=$pid name=Rpid hidden> <input type=submit
					value=Восстановить>
			</form></td>
		<td width='10%'><a
			href='../$current_print_folder/print.php?paper=$paper&year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&pid=$pid'
			target='_blank'>Печатать</a></td>
	</tr>";
}
if($table_started){
	echo "</table>";
}

include("footer.php");
?>
