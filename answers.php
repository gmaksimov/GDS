<?php
require('header_req.php');
$NO_TINYMCE = 1;
include('header.php');
//show answers in textarea
$year = $_GET['year'];
$halfyear = $_GET['halfyear'];
$grade = $_GET['grade'];
$booklet = $_GET['booklet'];
$paper = $_GET['paper'];
$sql = "SELECT PID, Subject FROM Tests WHERE Year='$year' AND Halfyear='$halfyear' AND Grade='$grade' AND Booklet='$booklet' AND Paper='$paper' AND Deleted=0 ORDER BY Position";
$result1 = $mysqli->query($sql) OR my_die("Error selecting PID of Tests".$mysqli->error);
echo"Ответы к тесту: $year года, $halfyear полугодия, $grade класса, буклета $booklet, теста с названием $paper<br>";
echo "<textarea cols = '20' rows = '30'>";
while($row1 = $result1->fetch_array()){
	$subject = $row1['Subject'];
	$pid = $row1['PID'];
	echo "$subject:\n";
	$sql = "SELECT * FROM Tasks WHERE tpid = $pid ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Error selecting answers: ".$mysqli->error);
	while($row = $result->fetch_array()){
		$ans = $row['Answer'];
		$pos = $row['Position'] / 10;
		$ansr ="";
		if($ans == 1){
			$ec = 'A';
		}else if($ans == 2){
			$ec = 'B';
		}else if($ans == 3){
			$ec = 'C';
		}else if($ans == 4){
			$ec = 'D';
		}else{
			my_die("Error! Answer != 1-4");
		}
		
		//echo"$pos: $ans \n";
		echo"$ec";
	}
	echo"\n\n";
}
echo"</textarea>";
include("footer.php");
?>