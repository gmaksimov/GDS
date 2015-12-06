<?php
require('../functions.php');
connect_to_mysql();
require('../session.php');
require('tfpdf.php');
  
if(!check_privilegies(-1)){
  my_die("Нужно право -1 для печти чего либо", "error");
}

class PDF extends tFPDF
{
//begin HTML using functions

var $B;
var $I;
var $U;
var $HREF;
var $edf;
var $sb;
var $sp;

function PDF($orientation='P', $unit='mm', $size='A4')
{
    // Call parent constructor
    $this->TFPDF($orientation,$unit,$size);
    // Initialization
    $this->B = 0;
    $this->I = 0;
    $this->U = 0;
    $this->HREF = '';
    $this->sp = 0;
    $this->sb = 0;
}

function WriteHTML($html, $check)
{
    // HTML parser
    $w = array(10, 70, 10);
    $html = str_replace("</p>", "<br>", $html);
    $html = str_replace("<p>", "", $html);
    $html = str_replace("<strong>","<B>",$html);
    $html = str_replace("</strong>","</B>",$html);
    $html = str_replace("<em>","<I>",$html);
    $html = str_replace("</em>","</I>",$html);
    $html = str_replace("<span style=\"text-decoration: underline;\">","<U>",$html);
    $html = str_replace("</span>","</U>",$html);
    $html = str_replace("\n",' ',$html);
    $html = str_replace("&nbsp;", ' ', $html);
    $a = preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            // Text
            if($this->HREF){
                $this->PutLink($this->HREF,$e);
            }else if ($this->sb==1){
                $y2 = $this->GetY();
                $x2 = $this->GetX();
                $this->SetY($y2+1);
                $this->SetX($x2);
                $this->SetFontSize(6);
                $this->Write(7, $e);
                $this->SetY($y2);
                $this->SetX($x2);
                $this->SetFontSize(10);
            }else if ($this->sp==1){
                $y1 = $this->GetY();
                $x1 = $this->GetX();
                $this->SetY($y1-2);
                $this->SetX($x1);
                $this->SetFontSize(6);
                $this->Write(7, $e);
                $this->SetY($y1);
                $this->SetX($x1);
                $this->SetFontSize(10);
            }else if ($check == 'lesson'){
                $this->SetFontSize(13);
                $this->Write(6, $e);
                $this->SetFontSize(10);
            }else if ($check == 'question'){
                $this->Write(6,$e);
            }else{
                $this->Write(6,$e);
            }
        }
        else
        {
            // Tag
          if($e[0]=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                // Extract attributes
                $a2 = explode(' ',$e);
                $tag = strtoupper(array_shift($a2));
                $attr = array();
                foreach($a2 as $v)
                {
                    if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
                        $attr[strtoupper($a3[1])] = $a3[2];
                }
                $this->OpenTag($tag,$attr);
            }
        }
    }
    $this->Ln();
}

function OpenTag($tag, $attr)
{
    // Opening tag
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,true);
    if($tag=='A')
        $this->HREF = $attr['HREF'];
    if($tag=='BR')
        $this->Ln(3);
    if($tag=='SUB')
        $this->sb = 1;
    if($tag=='SUP')
        $this->sp = 1;
}

function CloseTag($tag)
{
    // Closing tag
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF = '';
    if($tag=='SUB')
        $this->sb = 0;
    if($tag=='SUP')
        $this->sp = 0;
}

function SetStyle($tag, $enable)
{
    // Modify style and select corresponding font
    $this->$tag += ($enable ? 1 : -1);
    $style = '';
    foreach(array('B', 'I', 'U') as $s)
    {
        if($this->$s>0)
            $style .= $s;
    }
    $this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
    // Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}

//end of HTML using functions

function FPage() //Prints First Page
{
    $this->Addpage();
    global $mysqli;
    $year = $_GET['year'];
    $grade = $_GET['grade'];
    $booklet = $_GET['booklet'];
    $halfyear = $_GET['halfyear'];
    $paper = $_GET['paper'];
    $this->SetFontSize(50);
    $this->SetStyle('B', true);
    $this->SetY(70);
    $this->Cell(70);
    $this->Cell(50, 30,$paper,0,0,'C');
    $this->SetStyle('B', false);
    $this->SetFontSize(10);
    $this->SetTextColor(255,255,255);
    $this->Write(6,'1 2 3 4 5 6 7 8 9 0');
    $this->SetTextColor(0);
    $this->Ln();
    $sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Deleted=0 ORDER BY Position";
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
    /*    
    В $test_count хранится количество предметов.
    В $test_arr хранится информация о самих предметах.*/
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
    foreach($test_arr as $t){
        $this->Cell(50);
        $this->Cell(80, 6,$t['Name'],1,0,'C');
        //$this->Cell(10, 6,$t['Count'],1,1,'C');
        $this->Cell(13, 6, $t['Time'], 1, 1, 'C');
    }
}

function lesson($lesson)
{
    //Title
    if($edf == 0){
        $this->WriteHTML($lesson, 'lesson');    
        $this->Ln(2);
        $edf = 1;
    }else{
        $this->Ln();
        $this->WriteHTML($lesson, 'lesson');    
        $this->Ln(2);
    }
}

function Header()
{
    if($this->PageNo() == 1){
    }else{
        // Arial bold 15
        $this->SetFont('Arial','I',8);
        // Move to the right
        $this->Cell(70);
        $year = $_GET['year'];
        $grade = $_GET['grade'];
        $booklet = $_GET['booklet'];
        $halfyear = $_GET['halfyear'];
        // Title
            $this->Cell(50,8,$grade.' grade, booklet '.$booklet.', '.$year.' year',0,0,'C');
        $this->Ln();
    }
}

function Footer()
{
    if($this->PageNo() == 1){
        $this->SetY(-25);
	$this->Cell(70);
	$this->SetFont('DejaVu', 'I', 8);
	$this->Cell(50, 10,"Generated by GGds",0,false,'C');
	$this->Ln();
	$this->Cell(70);
	$this->Cell(50, 10,"GGds was made by Ilgiz Mustafin and Grigoriy Maksimov",0,false,'C');
	$this->SetFont('DejaVu', '', 10);
    }else{
        // Position at 1.5 cm from bottom
        $this->SetY(-15);
        // Arial italic 8
        $this->SetFont('Arial','I',8);
        // Page number
        $this->SetX(-40);
        $this->Cell(30,10,'Page '.$this->PageNo().'/{nb}',0,0,'R');
    }
}

function SetCol($col)
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

function AcceptPageBreak()
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
}

function question($quest, $ans, $num, $picture)
{
    // Column widths
    $w = array(10, 70, 10);
    $wor = array('A)', 'B)', 'C)', 'D)');
        // Header
        $this->Cell($w[0],6,$num, 1, 0,'C');
        $this->WriteHTML($quest, 'question');
    // Data
        if($picture){
            $s = getimagesize("../$picture");
            $ap = $s[0]/$s[1];
            $s[1] = 80/$ap;
            $sgd = $this->gety();
            if($sgd + $s[1] > 297){
                $this->SetCol($this->col+1);
                $this->sety(17);
            }
            $this->image("../$picture",$this->getx(),$this->gety()+1,80);
            $this->sety($this->gety()+$s[1]);
            $this->Ln();
        }
    for($i = 0; $i < 4; $i++)
    {
        $this->Cell($w[0],6,$wor[$i]);
        //$this->Cell($w[2],6,'',1);             
        $this->WriteHTML($ans[$i], 'ans');
        //$this->multicell($w[1],6,$ans[$i]);
    }
}
}
$pdf = new PDF();
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->SetFont('DejaVu', '', 10);
$pdf->FPage();
$pdf->AddPage();
$pdf->SetRightMargin(100);
$pdf->AliasNbPages();
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
    
