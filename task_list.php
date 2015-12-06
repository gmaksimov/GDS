<?php
/**
 * This page is for diplaying tasks
 */

require("header_req.php");

//get tpid
if(isset($_GET['tpid'])){
	$tpid = $_GET['tpid'];
}else{
	my_die("Не дан PID");
}

//Check privilegies for watching, deleting, making, swaping tasks
if(!check_privilegies($tpid)){
	my_die("У вас нет права на просмотр этого предмета, нужно $tpid");
}

//Deletes Task
if(isset($_POST['Dpid'])){
	$pid = $_POST['Dpid'];
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
	header("Location: edit_task.php?new=$tpid");
}

//swap
if(isset($_POST['first']) && isset($_POST['second'])){
	$first = addslashes($_POST['first']);
	$second = addslashes($_POST['second']);
	$sql = "SELECT Position FROM Tasks WHERE PID=$first";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$pos1 = $row['Position'];

	$sql = "SELECT Position FROM Tasks WHERE PID=$second";
	$result2 = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result2->fetch_array();
	$pos2 = $row['Position'];

	//assign new position, +- 5, because position differs in 10
	if($pos1 > $pos2){
		$new_pos = $pos2 - 5;
	}else{
		$new_pos = $pos2 + 5;
	}

	//get tpid for refreshing positions
	$sql = "SELECT Tpid FROM Tasks WHERE PID=$first";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	$row = $result->fetch_array();
	$tpid = $row['Tpid'];

	//update only one, second will be in right place
	$sql = "UPDATE Tasks SET Position=$new_pos WHERE PID=$second";
	if(!$mysqli->query($sql)){
		my_die("Ошибка обновления позиции: ".$mysqli->error);
	}

	//update positions (make them differ in 10 again)
	refresh_task_positions($tpid);

	header("Location: {$_SERVER['REQUEST_URI']}");
}

include('header.php');

//get vars
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
echo "
<table border=1>
	<div class=caption_table>
		Список заданий предмета <font style='color: rgb(45, 0, 255)'>$subject</font>
		<a
			href='print4/print.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&paper=$paper&pid=$tpid'
			target='_blank' class='capt' style='color: #6080f0;'>печать</a>
	</div>
	<tr>
		<th>Вопрос</th>";

if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
	echo"<th>PID</th>";
}

echo"
		<th width='15%' colspan=2>
			<form method=POST>
				<input type=text name=test_id value='$tpid' hidden=hidden> <input
					type=submit value='Создать задание [q]' title='(Accesskey: q)'
					accesskey='q' s>
			</form>
		</th>
		<th width='10%'><a href='make_answers.php?pid=$tpid'>редактировать
				ответы</a>
		</th>
	</tr>";

//get tasks
$sql = "SELECT * FROM Tasks WHERE Tpid = $tpid ORDER BY Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);

//print tasks
while($row = $result->fetch_array()){
	$position = $row['Position'];
	$question = $row['Question'];
	$pid = $row['PID'];
	
	//cut question
	$question = strip_tags($question);
	$MAX_LEN = 200;
	if(mb_strlen($question) > $MAX_LEN) {
		$question = mb_substr($question, 0, $MAX_LEN - 3);
		$lastSpacePosition = strrpos($question, ' ');
		$question = substr($question, 0, $lastSpacePosition)."<b> ...</b>";
	}
	
	echo "<tr>
			<td>$question</td>";
	
	if(isset($SHOW_PRIVILEGIES) && $SHOW_PRIVILEGIES == 1){
		echo"<td>$pid</td>";
	}
	
	echo "	<td width=10>
				<form method=POST>
   					<input name=first hidden value=$pid>
					<select name='second' onchange='this.form.submit()'>";
	
	//print 'select' for swapping
	select_task_tpid($tpid, $pid);
	
	echo "			</select>
				</form>
			<td>
				<form method=POST>
	  				<input type=text hidden value=$pid name=Dpid>
	  				<input onclick=\"return confirm('Вы действительно хотите удалить вопрос?')\" type=submit value=Удалить>
	  			</form>
  			</td>
  			<td>
				<a href='edit_task.php?pid=$pid'>Редактировать</a>
			</td>
		</tr>";
}

echo "</table>";

include('footer.php');
?>