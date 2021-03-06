<?php

require_once('../functions.php');

connect_to_mysql();

require('../session.php');
require_once('../tcpdf/tcpdf.php');

//check privilegies
if(isset($_GET['pid'])){

	$pid = addslashes($_GET['pid']);

	if(!check_privilegies($pid)){
		my_die("Ошибка, у вас нет права доступа на печать этого предмета, нужно $pid");
	}
}else if(!check_privilegies("-1")){
	my_die("Ошибка, у вас нет права доступа на печать этого теста, нужно -1");
}

function printTask($task, $task_num) {
	global $pdf;
	$question = $task['Question'];
	$ans[0] = $task['Ans1'];
	$ans[1] = $task['Ans2'];
	$ans[2] = $task['Ans3'];
	$ans[3] = $task['Ans4'];
	$picture = $task['Picture'];
	$question = str_replace("</p>", "<br>", $question);
	$question = str_replace("<p>", "", $question);
	$text = "<table border=\"1\" width=\"25px\" nobr=\"true\">
				<tr>
					<td style=\"text-align: center; width: 25px\">
					$task_num
					</td>
				</tr>
			</table>
			$question";
			if($picture) {
				$text .= "<img src=\"../$picture\"><br>";
			}
			for($i = 0; $i < 4; $i++){
				$letter = chr(ord("A") + $i);
				$text .= "<table border=\"0\" width=\"465px\" nobr=\"true\">
		<tr>
			<td style=\"text-align: center; width: 21px\">
			$letter)
			</td>
			<td style=\"text-align: left;\">
				".$ans[$i]."
			</td>
		</tr>";
			}
			$text .= "</table>";
			/*$text =
			 //		[<i>$task_num</i>]
			 //"<div style=\"display: inline-block; border: 1px solid black;\">$task_num</div>
			 "<table border=\"1\" width=\"10%\" nobr=\"true\"><tr><td style=\"text-align: center;\">$task_num</td></tr></table>
			 <p>$question</p>";
			 if($picture) {
			 $text .= "<p><img src=\"../$picture\"></p>";
			 }
			 $text .= "<ol type=\"A\">
			 <li>$ans1</li>
			 <li>$ans2</li>
			 <li>$ans3</li>
			 <li>$ans4</li>
			 </ol><br>";*/
			/* 	echo $text."<hr>"; */
			$pdf->WriteHTML($text);
}

/**
 * Prints one task.
 *
 * Use too much space, but code looks elegant (using html table support in TCPDF)
 *
 * @param object $task Contains all about task (question, answers, picture, etc.)
 * @param int $task_num Number of task
 */
function wastefullPrintTask($task, $task_num){
	global $pdf;

	//get vars
	$question = $task['Question'];
	$ans1 = $task['Ans1'];
	$ans2 = $task['Ans2'];
	$ans3 = $task['Ans3'];
	$ans4 = $task['Ans4'];
	$picture = $task['Picture'];

	//make header of task
	$text = "
		<table border=\"1\" width=\"10%\" nobr=\"true\"><tr><td style=\"text-align: center;\">$task_num</td></tr></table>
		<p>$question</p>";

	//add picture, if exists
	if($picture) {
		$text .= "<p><img src=\"../$picture\"></p>";
	}

	//add answers
	$text .= "<ol type=\"A\">
  		<li>$ans1</li>
  		<li>$ans2</li>
		<li>$ans3</li>
		<li>$ans4</li>
		</ol>";

	/*if($picture) {
		$text .= "<img src=\"../$picture\">";
		}*/

	//print task to pdf
	$pdf->WriteHTML($text);
}

function printTest($test) {
	global $mysqli;
	global $pdf;

	$subject = $test['Subject'];
	$pid = $test['PID'];
	$sql = "SELECT * FROM Tasks WHERE Tpid='$pid' ORDER BY Position";
	$all_tasks = $mysqli->query($sql) OR my_die("Error selecting: ".$mysqli->error);

	$text = "<h1>$subject</h1>";
	$pdf->WriteHTML($text);

	$task_num = 1;
	while($task = $all_tasks->fetch_array()) {
		printTask($task, $task_num);
		//	wastefullPrintTask($task, $task_num);
		$task_num++;
	}
}

function OldFPage() {
	global $pdf;
	global $mysqli;
	global $year;
	global $grade;
	global $booklet;
	global $halfyear;
	global $paper;
	$pdf->Addpage();
	$pdf->SetFont('', 'I', 30);
	$pdf->SetY(20);
	$pdf->Cell(0, 30, $_GET['year'], 0, 0, 'C');
	$pdf->SetFont('', 'B', 50);
	$pdf->SetY(70);
	$pdf->Cell(0, 30,$paper,0,0,'C');
	$pdf->SetFont('', '', 10);
	$sql = "SELECT * FROM Tests WHERE
		Year='$year' AND
		Grade='$grade' AND
		Booklet='$booklet' AND
		Halfyear='$halfyear' AND
		Paper='$paper' AND
		Deleted=0 ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die($mysqli->error);
	$test_count = $result->num_rows; //How many tests do we have
	$pdf->SetY(100);
	$pdf->SetFontSize(30);
	$sf = GetOrdinalSuffix($grade); //from ../functions.php
	$pdf->Cell(0, 20,"$grade$sf grade",0,1,'C');
	$pdf->SetFontSize(25);
	$pdf->Cell(0,20,"Booklet $booklet",0,1,'C');
	$pdf->SetFontSize(10);
	$test_arr = array();
	while($row = $result->fetch_array()){
		$pid = $row['PID'];
		$sql2 = "SELECT * FROM Tasks WHERE Tpid='$pid'";
		$result2 = $mysqli->query($sql2) OR my_die($mysqli->error);
		$test_arr[$row['PID']]['Name'] = $row['Subject'];
		//$test_arr[$row['PID']]['Time'] = $row['Time'];
		$test_arr[$row['PID']]['Count'] = $result2->num_rows;
	}
	$pdf->SetY(180);
	foreach($test_arr as $t){
		$pdf->Cell(49);
		$pdf->Cell(80, 6, $t['Name'],1,0,'C');
		$pdf->Cell(12, 6, $t['Count'],1,1,'C');
		//$pdf->Cell(12, 6, $t['Time'], 1, 1, 'C');
	}
}

class GDSPDF extends TCPDF {
	public function Footer() {
		$this_page = $this->GetPage() - 1;
		$all_pages_alias = $this->GetAliasNbPages();

		if(isset($_GET['pid']) && $this_page == 0){
			return;
		}

		if($this_page == 0) {
			$this->SetY(-25);
			//$pdf->MyCell(70);
			$this->SetFont('', 'I', 8);
			$text = "<div style=\"width: 100%; text-align: center\">Generated by GGds</div>
					<div style=\"width: 100%; text-align: center\"><b>GGds</b> by Ilgiz Mustafin and Grigoriy Maksimov</div>";
			$this->WriteHTML($text);
			$this->SetFont('', '', 10);
			return;
		}
		$align = "";
		if($this_page % 2 == 0) {
			$align = "left";
		} else {
			$align = "right";
		}
		$this->SetFont('', '', 9);
		$align = "center";

		$text = "<div style=\"width: 100%; text-align: $align; height: 30px\">
		$this_page/$all_pages_alias
				</div>";
		$this->SetY(-15);
		$this->WriteHTML($text);
		$this->SetFont('', '', 10);
	}

	public function Header() {
		$this_page = $this->GetPage() - 1;
		if($this_page == 0){
			return;
		}
		$this->SetY(7);
		$this->SetFont('', 'I', 8);
		$year = $_GET['year'];
		$grade = $_GET['grade'];
		$booklet = $_GET['booklet'];
		$halfyear = $_GET['halfyear'];
		$text = "<div style=\"width: 100%; text-align: center; height: 30px\">$grade grade, booklet $booklet, $year year</div>";
		$this->WriteHTML($text);
		$this->SetFont('', '', 10);
	}
}



if(!isset($_GET['year'])) {
	my_die("Не выбран год");
}
$year = addslashes($_GET['year']);
$grade = addslashes($_GET['grade']);
$booklet = addslashes($_GET['booklet']);
$halfyear = addslashes($_GET['halfyear']);
$paper = addslashes($_GET['paper']);
//$pid = $_GET['pid'];

$pdf = new GDSPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetStartingPageNumber(0);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('GrafonGDS');
/* $pdf->SetTopMargin(15);
 $pdf->SetAutoPageBreak(true, 25); */
//$pdf->SetMargins(10, 17, 10);
//$pdf->SetHeaderMargin(10);
//$pdf->SetFooterMargin(10);
$main_font_family = "dejavusans";
$main_font_size = 10;
$pdf->SetFont($main_font_family, '', $main_font_size);

//print only one subject
if(isset($pid)){
	$andpid="AND PID='$pid'";
} else {
	OldFPage();
}

$sql = "SELECT * FROM Tests WHERE
	Year='$year' AND
	Grade='$grade' AND
	Booklet='$booklet' AND
	Halfyear='$halfyear' AND
	Paper='$paper' AND
	Deleted=0 $andpid ORDER BY Position";

$all_tests = $mysqli->query($sql) OR my_die("Error selecting test: ".$mysqli->error);

$pdf->SetMargins(10, 17, 10);

$pdf->AddPage();
//$pdf->SetBooklet(true);
$pdf->SetEqualColumns(2, 90);

$pdf->SelectColumn();


while($test = $all_tests->fetch_array()) {
	printTest($test);
}

$pdf->Output();

