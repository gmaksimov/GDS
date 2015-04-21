<?php
require("header_req.php");

//assign tpid
if(isset($_GET['tpid']))
 $tpid = $_GET['tpid'];
else
 my_die("Не дан PID");

if(!check_privilegies($tpid)){
	my_die("У вас нет права на просмотр этого предмета, нужно $tpid");
}

//Deletes Task
if(isset($_POST['Dpid'])){
  $pid = $_POST['Dpid'];
  if(!check_privilegies($tpid)){
    my_die("У вас нет права на удаление этого задания, нужно $tpid");
    return;
  }
  $sql = "DELETE FROM Tasks WHERE PID=$pid";
  $result = $mysqli->query($sql) OR my_die("Ошибка удаления задания: ".$mysqli->error);
  if(isset($_GET['tpid'])){
    refresh_task_positions($tpid);
  } else {
    show_message("Нету tpid в GET, используйте refresh_positions, ok?", "error");
  }
}

//makes task
if(isset($_POST['test_id'])){
  $tpid = addslashes($_POST['test_id']);
  if(!check_privilegies($tpid)){
    my_die("У вас нет права на создание задания в этом предмете, нужно $tpid");
    return;
  }
  header("Location: edit_task.php?new=$tpid");
  //show_message("Task $last_id inserted <a href='edit_task.php?pid=$last_id'>Редактировать</a>", "ok");
}


//swap
if(isset($_POST['first']) && isset($_POST['second'])){
    $sql = "SELECT Position FROM Tasks WHERE PID={$_POST['first']}";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$pos1 = $row['Position'];
    $sql = "SELECT Position FROM Tasks WHERE PID={$_POST['second']}";
    $result2 = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
    $row = $result2->fetch_array();
    $pos2 = $row['Position'];
	if($pos1 > $pos2){
		$new_pos = $pos2 - 5;
	}else{
		$new_pos = $pos2 + 5;
	}
	$sql = "SELECT Tpid FROM Tasks WHERE PID={$_POST['first']}";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$tpid = $row['Tpid'];
    $sql = "UPDATE Tasks SET Position=$new_pos WHERE PID={$_POST['first']}";
    if(!$mysqli->query($sql)){
        my_die("Ошибка обновления позиции: ".$mysqli->error);
    }
    refresh_task_positions($tpid);
    header("Location: {$_SERVER['REQUEST_URI']}");
}
//header
include('header.php');
//get subj
$sql = "SELECT * FROM Tests WHERE PID=$tpid";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$row = $result->fetch_array();
$subject = $row['Subject'];
$year = $row['Year'];
$halfyear = $row['Halfyear'];
$grade = $row['Grade'];
$booklet = $row['Booklet'];
$paper = $row['Paper'];
//show table
$sql = "SELECT * FROM Tasks WHERE Tpid = $tpid ORDER BY Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
echo "<table border=1><div class=caption_table>Список заданий предмета <font style='color: rgb(45, 0, 255)'>$subject</font>  <a href='print4/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper&pid=$tpid' target = '_blank' class='capt' style='color: #6080f0;'>печать</a></div>
<tr><th>Вопрос</th>";
if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
	echo"<th>PID</th>";
}
echo"
<th width='15%' colspan=2>
	<form method=POST>
		<input type=text name=test_id value='$tpid' hidden=hidden>
		<input type=submit value='Создать задание [q]' title='(Accesskey: q)' accesskey='q's>
	</form>
</th>
<th width='10%'>
	<a href='make_answers.php?pid=$tpid'>редактировать ответы</a>
</th>";
echo"</tr>";
while($row = $result->fetch_array()){
  	$position = $row['Position'];
 	$question = $row['Question'];
	$question = strip_tags($question);
	$MAX_LEN = 200;
	if(mb_strlen($question) > $MAX_LEN) {
		$question = mb_substr($question, 0, $MAX_LEN - 3);
		$lastSpacePosition = strrpos($question, ' ');
		$question = substr($question, 0, $lastSpacePosition)."<b> ...</b>";
	}
	$pid = $row['PID'];
  	echo "<tr><td>$question</td>";
  	if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
  		echo"<td>$pid</td>";
  	}
  	echo "<td width=10><form method=POST>
   		<input name=first hidden value=$pid>
		<select name='second' onchange='this.form.submit()'>";
	select_task_tpid($tpid, $pid); //from functions.php
  	echo "</select></form>";
  	echo "<td><form method=POST>
  		<input type=text hidden value=$pid name=Dpid>
  		<input onclick=\"return confirm('Вы действительно хотите удалить вопрос?')\" type=submit value=Удалить>
  		</form></td><td>
		<a href='edit_task.php?pid=$pid'>Редактировать</a>
		</td></tr>";
}
echo "</table>";

include('footer.php');
?>
	