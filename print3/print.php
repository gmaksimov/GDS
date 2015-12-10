<?php
require('../functions.php');
connect_to_mysql();
require('../session.php');
require_once('../tcpdf/tcpdf.php');

if(!check_privilegies("-1")){
  my_die("you need -1 to print this");
}

class MYPDF extends TCPDF {

var $edf;
var $col;
var $allfont;

  public function MyCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $stretch = 0, $ignore_min_height = false, $calign = 'T', $valign = 'M'){
	$this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $stretch, $ignore_min_height, $calign, $valign);
	$this->writeHTMLCell(0.0000001, 0.0000001, $this->GetX(), $this->GetY()+0.9, '', 0);
  }
  
  //makes special header
  public function Header() {
    if($this->getPage() != 1){
        // Arial bold 15
        $this->SetFont($allfont, 'I', 8);
        $year = $_GET['year'];
        $grade = $_GET['grade'];
        $booklet = $_GET['booklet'];
        $halfyear = $_GET['halfyear'];
        // Title
        $this->MyCell(70);
        $this->MyCell(50,8,"$grade grade, booklet $booklet, $year year",0,0,'C',false,'',0,false);
        $this->Ln();
        $this->SetFont($allfont, '', 10);
    }
  }
  //makes special footer
  public function Footer()
  {
    if($this->getPage() == 1){
		$this->SetY(-25);
		$this->MyCell(70);
		$this->SetFont($allfont, 'I', 8);
		$this->MyCell(50, 10,"Generated by GGds",0,false,'C');
		$this->Ln();
		$this->MyCell(70);
		$this->MyCell(50, 10,"GGds was made by Ilgiz Mustafin and Grigoriy Maksimov",0,false,'C');
		$this->SetFont($allfont, '', 10);
	}else{
      // Position at 1.5 cm from bottom
      $this->SetY(-15);
      $this->SetX(-10);
      // Arial italic 8
      $this->SetFont($allfont, 'I', 8);
      // Page number
      $this->MyCell(10, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(),0,false,'R');
      $this->SetFont($allfont, '', 10);
    }
  }

  //print subject name

  public function lesson($lesson)
  {
    //Title
    if($edf == 0){
      $this->SetFontSize(13);
      $this->writeH($lesson);
      $this->Ln(2);
      $edf = 1;
      $this->SetFontSize(10);
    }else{
      $this->SetFontSize(13);
      $this->Ln();
      $this->WriteH($lesson);    
      $this->Ln(2);
      $this->SetFontSize(10);
    }
  }

  //prints one question $quest - text of question, $ans - massive of four answers, $num - number of question, $picture - adress of picture

  public function question($quest, $ans, $num, $picture)
  {
    // Column widths
    $w = array(10, 70, 10);
    $wor = array('A)', 'B)', 'C)', 'D)');
    // Header
    $this->SetFontSize(10);
    $this->MyCell($w[0],6,$num, 1, 0,'C');
    $this->MyCell(1, 6);
    $this->writeH($quest);
    // Data
   if($picture){
            $s = getimagesize("../$picture");
            $ap = $s[0]/$s[1];
            $s[1] = 80/$ap;
            $sgd = $this->gety();
            /*if($sgd + $s[1] > 297){
                $this->SetCol($this->col+1);
                $this->sety(17);
            }*/
            $this->image("../$picture",$this->getx(),$this->gety()+1,80);
            $this->sety($this->gety()+$s[1]);
            $this->Ln();
        }
    for($i = 0; $i < 4; $i++)
    {
        $this->SetFontSize(10);
        $this->MyCell($w[0],6,$wor[$i]);
        $this->writeH($ans[$i]);
    }
	//$this->Ln();
  }

  public function WriteH($html)
  {
    $html = str_replace("</p>", "<br>", $html);
    $html = str_replace("<p>", "", $html);
/*                                        ////////////////////////
    $html = str_replace("<b>", "<em>", $html);
    $html = str_replace("</b>", "</em>", $html);

    $html = str_replace("<i>", "<em>", $html);
    $html = str_replace("</i>", "</em>", $html);
    $html = str_replace("<u>", '<span style="text-decoration: underline;">', $html);
    $html = str_replace("</u>", "</span>", $html);
*/                                        ////////////////////////
    $this->WriteHTML($html, true, false, false, false, '');
  }
 /* public function SetCol($col)
  {
    // Set position at a given column
    $this->col = $col;
    $x = 10 + $col * 100;
    $this->SetLeftMargin($x);
    if($col == 0){
        $this->SetRightMargin(110);
    }else{
        $this->SetRightMargin(10);
    }
    $this->SetX($x);
  }

  public function AcceptPageBreak()
  {
    // Method accepting or not automatic page break
    if($this->col<1)
    {
        // Go to next column
        $this->SetCol($this->col+1);
        // Set ordinate to top
        $this->SetY($this->y0 + 17);
        // Keep on page
        return false;
    }
    else
    {
        // Go back to first column
        $this->SetCol(0);
        // Page break
        return true;
    }
  }*/

  public function FPage() //Prints First Page
  {
    $this->Addpage();
    global $mysqli;
    $year = $_GET['year'];
    $grade = $_GET['grade'];
    $booklet = $_GET['booklet'];
    $halfyear = $_GET['halfyear'];
    $paper = $_GET['paper'];
    $this->SetFont($allfont, 'I', 30);
    $this->Cell(70);
    $this->Cell(50, 30, $_GET['year'], 0, 0, 'C');
    $this->SetFont($allfont, 'B', 50);
    $this->SetY(70);
    $this->Cell(70);
    $this->Cell(50, 30,$paper,0,0,'C');
    $this->SetFont($allfont, '', 10);
    $this->Ln();
    $sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Paper='$paper' AND Deleted=0 ORDER BY Position";
    $result = $mysqli->query($sql) OR my_die($mysqli->error);
    $test_count = $result->num_rows; //How many tests do we have
    $test_arr = array();
    while($row = $result->fetch_array()){
        $pid = $row['PID'];
        $sql2 = "SELECT * FROM Tasks WHERE Tpid='$pid'";
        $result2 = $mysqli->query($sql2) OR my_die($mysqli->error);
        $test_arr[$row['PID']]['Name'] = $row['Subject'];
		$test_arr[$row['PID']]['Time'] = $row['Time'];
        $test_arr[$row['PID']]['Count'] = $result2->num_rows;
    }
    $this->SetY(100);
    $this->SetFontSize(30);
    $this->Cell(70);
    $suffixes = array(0 => '', 'st', 'nd', 'rd', 'th');
    if($grade == '13'){
      $suffix = 4;
    } else if($grade == '11'){
      $suffix = 4;
    } else if($grade == '12'){
      $suffix = 4;
    } else if($grade == '0'){
      $suffix = 0;
    } else if((int)$grade % 10 == 1){
      $suffix = 1;
    } else if((int)$grade % 10 == 2){
      $suffix = 2; 
    } else if((int)$grade % 10 == 3){
      $suffix = 3; 
    } else {
      $suffix = 4; 
    }
    $sf = $suffixes[$suffix];
    $this->Cell(50, 20,"$grade$sf grade",0,1,'C');
    $this->SetFontSize(25);
    $this->Cell(70);
    $this->Cell(50,20,"Booklet $booklet",0,1,'C');
    $this->SetFontSize(10);
    $this->SetY(150);
    /*Table*/
    //$this->Cell(43);
    /*$this->Cell(80, 6, "Subj", 1, 0, 'C');
    $this->Cell(13, 6, "Tasks", 1, 0, 'C');
    $this->Cell(13, 6, "Time", 1, 1, 'C');*/
    foreach($test_arr as $t){
        $this->Cell(43);
        $this->Cell(80, 6, $t['Name'],1,0,'C');
        //$this->Cell(13, 6, $t['Count'],1,0,'C');
        $this->Cell(13, 6, $t['Time'], 1, 1, 'C');
    }
  }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->setPageOrientation('P',true,10);
//FONT
$allfont = "dejavusans";
//FONT
//set default subsetting  mode
$pdf->setFontSubsetting(false);
$pdf->SetFont($allfont, '', 10);
$pdf->resetcolumns();
$pdf->SetMargins(10, 17, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->FPage();
$pdf->SetEqualColumns(2, 90,'');
$pdf->AddPage();
//getting data
if(!isset($_GET['year']))
  my_die("Not set year");
$year = $_GET['year'];
$grade = $_GET['grade'];
$booklet = $_GET['booklet'];
$halfyear = $_GET['halfyear'];
$sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Deleted=0 ORDER BY Position";
$all_tests = $mysqli->query($sql) OR my_die("Error selecting test: ".$mysqli->error);
$edf = 0;
while($test = $all_tests->fetch_array()){
  $subject = $test['Subject'];
  $booklet = $test['Booklet'];
  $year = $test['Year'];
  $pid = $test['PID'];
  $sql = "SELECT * FROM Tasks WHERE Tpid='$pid' ORDER BY Position";
  $all_tasks = $mysqli->query($sql) OR my_die("Error selecting: ".$mysqli->error);
  $i = 0;
  $j = 1;
  $pdf->lesson($subject);
  while($task = $all_tasks->fetch_array()){
    $quest = $task['Question'];
    $ans[0] = $task['Ans1'];
    $ans[1] = $task['Ans2'];
    $ans[2] = $task['Ans3'];
    $ans[3] = $task['Ans4']; 
    $picture = $task['Picture'];
    $pdf->question($quest, $ans, $j, $picture);
    $j++;
  }
}
$pdf->Output();
?>	
		