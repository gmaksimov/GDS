<?php
require('header_req.php');

//Marks Test for Deletion in db

if(isset($_POST['Dpid'])){
    if(!check_privilegies("-1") && !check_privilegies($_POST['Dpid'])){
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
	if(isset($_POST['new']) && $_POST['new'] != NULL){
		$new = "&new=1";
	}else{
		$new = "";
	}
    //$sql = "INSERT INTO Tests (Year, Subject, Position) VALUES (0, 'UNNAMED', 10050000000)";
    //$result = $mysqli->query($sql) OR my_die("Ошибка создания предмета: ".$mysqli->error);
    //$last_id = $mysqli->insert_id;
    //refresh_test_positions();
    header("Location: edit_test.php?exam=$exam$new");
}

//Changing Position. Put after second
if(isset($_POST['first']) && isset($_POST['second'])){
    $sql = "SELECT Position FROM Tests WHERE PID={$_POST['first']}";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$pos1 = $row['Position'];
    $sql = "SELECT Position FROM Tests WHERE PID={$_POST['second']}";
    $result2 = $mysqli->query($sql) OR my_die("Ошибка получения данных".$mysqli->error);
    $row = $result2->fetch_array();
    $pos2 = $row['Position'];
	if($pos1 > $pos2){
		$new_pos = $pos2 - 5;
	}else{
		$new_pos = $pos2 + 5;
	}
    $sql = "UPDATE Tests SET Position=$new_pos WHERE PID={$_POST['first']}";
    if(!$mysqli->query($sql)){
        my_die("Ошибка обновления позиции предмета".$mysqli->error);
    }
    refresh_test_positions();
    header("Location: {$_SERVER['REQUEST_URI']}");
}

//header
include ('header.php');

//select exam
$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY Year DESC, Halfyear DESC, Paper DESC";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$rows = $result->num_rows;
$c = 1;
$ex = -1;
$pr_date = array(-1, -1, -1);
if(isset($_GET['exam'])){
	$exam = $_GET['exam'];
}else{
	$exam=NULL;
}
echo"<form method=GET>
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
		if($date !== $pr_date){
			if($exam == NULL && $c == 1){
					echo"<option value=$pid selected>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
					$ex = $pid;
			}else{
				if($exam == $pid){
						echo"<option value=$pid selected>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
				}else{
						echo"<option value=$pid>Год: $year, Полугодие: $halfyear, Тест: $paper</option>";
				}
			}
			$c=2;
		}
		$pr_date = $date;
	}
	echo"</select>
</form><br>";
if(check_privilegies("-1")){
	echo "<form method=POST>
	  <input type=text name=Create value=rly hidden=hidden>
	  <input type=text name=new value=rly hidden>
	  <input type=submit value=\"Создать тест[q]\" accesskey=q>
	</form>";
}
//show exam
if(!isset($_GET['exam']) && $ex == -1){
	my_die("Пожалуйста, выберите тест");
}else{
	if(isset($_GET['exam'])){
		$exam = $_GET['exam'];
	}else{
		$exam = $ex;
	}
}
$sql = "SELECT * FROM Tests WHERE PID='$exam'";
$result = $mysqli->query($sql);
$row = $result->fetch_array();
$year = $row['Year'];
$halfyear = $row['Halfyear'];
$paper = $row['Paper'];
$sql = "SELECT * FROM Tests WHERE Deleted=0 AND Paper ='$paper' AND Halfyear = '$halfyear' AND Year = '$year' ORDER BY Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$table1 = "<table border=1 width='70%'><div class=caption_table>";
$table2 = "</div><tr>";
$colsp = 0;
if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
	$table2 .="<th>PID</th>";
}
$table2 .= "<th>Предмет</th>";
if(check_privilegies("-1")){
	$table2 .= "
		<th>
			Кол-во заданий
		</th>
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
  $time = $row['Time'];
  $sql = "SELECT * FROM Tasks WHERE Tpid=$pid";
  $result1 = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
  $ans_count = $result1->num_rows;
  if(!check_privilegies($pid)){
    continue; 
  }
  $cur_date = array($year, $halfyear, $grade, $booklet, $paper);
  if($cur_date != $date){
    if($table_started){
        echo "</table>";
    }
    $date = $cur_date;
    $date_string = "Класс: $grade, Буклет: $booklet";
    if(check_privilegies("-1")){
      $date_string .= " <a href='print4/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper' target = '_blank' class=capt>Печать по-админски</a>
	  <a href='options.php?pid=$pid' class=capt>опции</a>
	  <a href='answers.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper' target = '_blank' class=capt>ответы</a>";
	}

    echo $table1.$date_string.$table2;
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
  if(check_privilegies("-1")){
    echo"
	<td width=3%>$ans_count</td>
    <td width=4%><form method=POST>
    <input name=first hidden value=$pid>
	<select name='second' onchange='this.form.submit()'>";
	select_test_pid($pid); //from functions.php
	echo"</select></form>";
  }
  echo"
  <td width='7.5%'><a href='task_list.php?tpid=$pid'>Задания</a></td>";
  if(check_privilegies("-1")){
  echo"
  <td width='7.5%'><form method=POST>
  <input type=text value=$pid name=Dpid hidden>
  <input onclick=\"return confirm('Вы действитель хотите удалить предмет?')\" type=submit value=Удалить>
  </form></td>
  <td width='8%'><a href='edit_test.php?pid=$pid'>Редактировать</a></td>";
  }
  echo"<td width='7.5%'><a href='print4/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper&pid=$pid' target = '_blank'>";
  echo"Печатать</a></td>
  </tr>";
}
if($table_started){
    echo "</table>";
}
  if(check_privilegies("-1")){
  echo"
<br>";
}
include('footer.php');
?>
