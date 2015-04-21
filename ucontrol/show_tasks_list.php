<?php
$sql = "SELECT * FROM Tests WHERE Deleted=0  ORDER BY Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
echo"<div style = \"display:inline-table;\"><table border = '1'><tr><th><b>PID</b></th><th>year</th><th>halfyear</th><th>grade</th><th>booklet</th><th>paper</th><th>subject</th></tr>";
while($row = $result->fetch_array()){
  $pid = $row['PID'];
  $year = $row['Year'];
  $halfyear = $row['Halfyear'];
  $subject = $row['Subject'];
  $grade = $row['Grade'];
  $booklet = $row['Booklet'];
  $pid = $row['PID'];
  $position = $row['Position'];
  $paper = $row['Paper'];
  $sql = "SELECT Answer FROM Tasks WHERE Tpid=$pid";
  $result1 = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$tasks_count = $result1->num_rows;
	$count = 0;
	while($row1 = $result1->fetch_array()){
		if($row1['Answer'] == 1){
			$count++;
		}
	}
	$no_tasks = "";
	$bad_answer = "";
	if($tasks_count == 0){ // should be <= $max_tasks
		$no_tasks = " title='NO TASKS' style='color: orange' ";
	}
	if($count >= $tasks_count * 0.8){
		$bad_answer = " title='NO ANSWERS' style='color: red' ";
	}
  echo"<tr><td$no_tasks><b>$pid</b></td><td>$year</td><td>$halfyear</td><td>$grade</td><td>$booklet</td><td>$paper</td><td$bad_answer>$subject</td></tr>";
}
echo "</table></div>";
