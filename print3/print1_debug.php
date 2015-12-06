<?php
require('../functions.php');
connect_to_mysql();
require('../session.php');
require_once('tcpdf.php');

$t1pid = $_GET['pid'];

if(!check_privilegies($t1pid)){
  my_die("you need $t1pid to print this", "error");
}
class MYPDF extends TCPDF {

var $edf;
var $col;
var $allfont;

  //makes special header
  public function Header() {
        // Arial bold 15
        $this->SetFont($allfont, 'I', 8);
        $year = $_GET['year'];
        $grade = $_GET['grade'];
        $booklet = $_GET['booklet'];
        $halfyear = $_GET['halfyear'];
        // Title
        $this->WriteHTMLCell(0, 0, 0, 0, "<b>$grade</b> grade, booklet $booklet, $year year", 0, 0, false, true, 'C');
        //$this->Ln();
        //$this->SetFont($allfont, '', 10);
  }
  //makes special footer
  public function Footer()
  {
      // Position at 1.5 cm from bottom
      $this->SetY(-15);
      $this->SetX(-10);
      // Arial italic 8
      $this->SetFont($allfont, 'I', 8);
      // Page number
      $this->Cell(10, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(),0,false,'R');
      $this->SetFont($allfont, '', 10);
  }

  //print subject name

  public function lesson($lesson)
  {
    //Title
    if($edf == 0){
      $this->SetFontSize(13);
      $this->writeH($lesson);
      $this->Ln();
      $edf = 1;
      $this->SetFontSize(10);
    }else{
      $this->SetFontSize(13);
      $this->Ln();
      $this->WriteH($lesson);    
      $this->Ln();
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
        $this->Cell($w[0],6,$num, 1, 0,'C');
        $this->writeH($quest);
    // Data
        if($picture){
            $s = getimagesize("../$picture");
            $ap = $s[0]/$s[1];
            $s[1] = 80/$ap;
            $sgd = $this->gety();
            $this->image("../$picture",$this->getx(),$this->gety()+1,80);
            $this->sety($this->gety()+$s[1]);
            $this->Ln();
        }
    for($i = 0; $i < 4; $i++)
    {
        $this->SetFontSize(10);
        $this->Cell($w[0],6,$wor[$i]);
        $this->writeH($ans[$i]);
    }
  }

  public function WriteH($html)
  {
    $html = str_replace("</p>", "<br>", $html);
    $html = str_replace("<p>", "", $html);
    $this->WriteHTML($html);
  }
}

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->setPageOrientation('P',true,10);
//FONT
$allfont = "dejavusans";
//FONT
//set default subsetting  mode
//$pdf->setFontSubsetting(false);
$pdf->SetFont($allfont, '', 10);
$pdf->resetcolumns();
$pdf->SetMargins(10, 17, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->AddPage();
$pdf->SetY(18);
$pdf->SetEqualColumns(2, 90,'');

$pdf->WriteHTML('<b>aazzazaz</b> lalka <i>bamba</i>');

$pdf->WriteHTML('&nbsp;allalkalkal');
$pdf->WriteHTML('&nbsp;allalkalkal');
$pdf->WriteHTML('&nbsp;allalkalkal');


//getting data
if(!isset($_GET['year']))
  my_die("Not set year");
$year = $_GET['year'];
$grade = $_GET['grade'];
$booklet = $_GET['booklet'];
$halfyear = $_GET['halfyear'];
$sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND PID='$t1pid' AND Deleted=0 ORDER BY Position";
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