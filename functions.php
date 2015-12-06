<?php
/**
 * This page is for functions, that are used in few pages
 */

/**
 *Shows a pop up notification.
 *
 *
 * @param string $msg message
 * @param string $type which icon to use
 *	ok (success) -> green
 *	error -> red
 *	info -> blue
 *	warning -> orange.
 * @param string $head the title of notification
 *	if not specifyed:
 *		head = type.
 * @param int $time (ms) how long it will be displaying
 *	-1 -> display 'forever'
 *	default values for types:
 *		ok (success) -> 5000
 *		error -> -1
 *		info -> 10000
 *		warning -> 5000.
 * @param bool $show_now
 *	false -> show after refresh or redirect
 *	true -> showing in the current page.
 * @return void
 */
function show_message($msg, $type="info", $head = "", $time = 5000, $show_now = false){
	switch ($type) {
		case "ok":
			$type = "success";
			break;
		case "error":
			$time = -1;
			break;
		case "info":
			$time = 10000;
			break;
		case "warning":
			$time = 5000;
			break;
	}

	if($head == ""){
		$head = $type;
	}

	if($time == -1){
		$time = 10000000;
	}

	if($show_now){
		global $MSG;
		$MSG[] = array("$head", "$msg", "$type", $time);
	}else{
		$_SESSION['MSG'][] = array("$head", "$msg", "$type", $time);
	}
}

/**
 * Returns a suffix of number.
 * Used in print.php
 * @param int $int Number
 * @return string Suffix
 */
function getOrdinalSuffix($int) {
	$suffixes = array(0 => '', 'st', 'nd', 'rd', 'th');
	if($int == '13'){
		$x = 4;
	} else if($int == '11'){
		$x = 4;
	} else if($int == '12'){
		$x = 4;
	} else if($int == '0'){
		$x = 0;
	} else if((int)$int % 10 == 1){
		$x = 1;
	} else if((int)$int % 10 == 2){
		$x = 2;
	} else if((int)$int % 10 == 3){
		$x = 3;
	} else {
		$x = 4;
	}
	return $suffixes[$x];
}


/**
 * Stops loading page; includes header.php; shows a pop up notification; includes footer.php.
 *
 * @param string $message
 * @param string $type which icon to use
 *	ok (success) -> green
 *	error -> red
 *	info -> blue
 *	warning -> orange
 * @param string $head the title of notification
 * @param int $time (ms) how long it will be displaying
 *	-1 -> display 'forever'
 *	default values for types:
 *		ok (success) -> 5000
 *		error -> -1
 *		info -> 10000
 *		warning -> 5000
 * @return void
 */
function my_die($message, $type = "error", $head = "Ошибка", $time = -1){
include_once('header.php');

if(isset($message) && $message != NULL){
	show_message($message, $type, $head, $time, true);
}

include_once('footer.php');
die();
}

/**
 * Print drop down list with 'subjects'.
 * 
 * Didn't use anywhere!
 * 
 * @param string $name Name of 'select', that will be printed
 * @param int $selected Which variant will be selected by default
 * @return void
 */
function select_test($name = "subject", $selected= -1){
	global $mysqli;
	
	$sql = "SELECT PID, Subject FROM Tests WHERE Deleted=0 ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
	
	if($result->num_rows == 0){
		show_message("Нет тестов", "error");
		return;
	}
	
	echo "<select name='$name'>";
	while($row = $result->fetch_array()){
		$pid = $row['PID'];
		$subject = $row['Subject'];
		
		if((int)$pid == (int)$selected){
			$mod = "selected=selected";
		} else {
			$mod = "";
		}
		echo "<option value='$pid' $mod>'$subject'</option>";
	}
	echo "</select><br>";
}

/**
 * Printes 'select' with nums from .. to and same option values
 * @param string $name Name of 'select' block
 * @param int $from Starting ID
 * @param int $to Ending ID
 * @param int $selected Which variant will be selected by default
 * @param string $accesskey Accesskey for the 'select' block
 * @param string $id ID of 'select' block
 * @return void
 */

function select_numbers($name, $from, $to, $selected, $accesskey = "", $id = "", $accesskeys = ""){
	if($accesskey != ""){
		$accesskey = "accesskey='$accesskey'";
	}
	if($id  != ""){
		$id = "id='$id'";
	}
	echo "<select name='$name' $accesskey $id>";
	for($i = (int)$from; $i <= (int)$to; $i++){
		if((int)$selected == (int)$i){
			$mod = "selected=selected";
		} else {
			$mod = "";
		}
		echo "<option value='$i' $mod ";
		/* if($accesskey != ""){
		 //echo "accesskey='{$accesskeys[$i]}'";
		 }*/
		echo ">$i</option>";
	}
	echo "</select><br>";
}


/**
 * Connects to mysql
 * @return void
 */
function connect_to_mysql(){
	global $mysqli;
	require_once('constants.php');
	global $_mysqli_user;
	global $_mysqli_pass;
	global $_mysqli_db;
	$mysqli = new mysqli("localhost",$_mysqli_user,$_mysqli_pass,$_mysqli_db);
	if(mysqli_connect_errno($mysqli)){
		my_die("Could not connect to MySql: ".$mysqli->error);
	} else {
		$mysqli->set_charset('utf8');
		//or maybe use {$mysqli->query('SET NAMES utf8') OR die(show_message("Error making utf8", "error"))} for utf8
	}
}

/**
 * Checks privilegies.
 * @param string|int $pr The priv ID
 * @return bool True, if user can modify 'subject', false otherwise
 */
function check_privilegies($pr){
	require_once('session.php');
	global $mysqli;

	$pr = (int)$pr;
	$login = $_SESSION['gds']['login'];

	$sql = "SELECT Privilegies FROM Users WHERE Login='$login'";
	if(!$result = $mysqli->query($sql)){
		show_message("Ошибка получения данных во время проверки прав доступа: ".$mysqli->error, "error");
		return 0;
	}
	if($result->num_rows == 0){
		show_message("Ошибка во время проверки прав доступа: нет пользователя с таким логином", "error");
		return 0;
	}
	$row = $result->fetch_array();

	$privilegies = $row['Privilegies'];
	$privs = preg_split("/[\s,]+/", $privilegies);

	foreach($privs as $p){
		if(($p == $pr) || ($p == "-1")){
			return 1;
		}
	}
	return 0;
}


/**
 * Prints 'select' with Test ID as value of option and 1 .. test_count as text
 * @param int $pid ID of 'subject'
 * @return void
 */
function select_test_pid($pid){
	global $mysqli;
	$sql = "SELECT * FROM Tests WHERE PID = '$pid'";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных для выбора PID'а: ".$mysqli->error);
	$row = $result->fetch_array();
	$year = $row['Year'];
	$halfyear = $row['Halfyear'];
	$grade = $row['Grade'];
	$booklet = $row['Booklet'];
	$paper = $row['Paper'];
	$sql = "SELECT PID FROM Tests WHERE Year = '$year' AND Halfyear = '$halfyear' AND Grade = '$grade' AND Booklet = '$booklet' AND Paper = '$paper' AND Deleted = 0 ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения PID'а: ".$mysqli->error);
	$rows = $result->num_rows;
	for($i = 1; $i <= $rows; $i++){
		$row = $result->fetch_array() OR show_message("Ошибка: ".$mysqli->error, "error");
		$zpid = $row['PID'];
		if($zpid == $pid){
			$mod = "selected=selected";
		} else {
			$mod = "";
		}
		echo"<option value='$zpid' $mod>$i</option>";
	}
}

/**
 * Prints 'select' with Tasks ID as value of option and 1 .. task_count as text
 * @param int $tpid ID of 'subject'
 * @param int $selected Which variant will be selected by default
 * @return void
 */
function select_task_tpid($tpid, $selected){
	global $mysqli;
	$sql = "SELECT * FROM Tasks WHERE Tpid=$tpid ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время получения Tpid'а: ".$mysqli->error);
	for($i = 1; $row = $result->fetch_array(); $i++){
		if($row['PID'] == $selected){
			$mod = "selected=selected";
		} else {
			$mod = "";
		}
		echo "<option value={$row['PID']} $mod>$i</option>";
	}
}

/**
 * Refreshes position of each 'subject' in the 'test'
 * @return void
 */
function refresh_test_positions(){
	global $mysqli;

	$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY Year, Halfyear, Grade, Booklet, Paper, Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время обноврения позиций тестов: ".$mysqli->error);

	$date = array();

	while($row = $result->fetch_array()){
		$cur_date = array($row['Year'], $row['Halfyear'], $row['Grade'], $row['Booklet'], $row['Paper']);

		//new test started
		if($cur_date != $date){
			$pos = 10;
		} else {
			$pos += 10;
		}

		$date = $cur_date;

		$sql = "UPDATE Tests SET Position=$pos WHERE PID={$row['PID']}";
		if(!$mysqli->query($sql)){
			my_die("Ошибка обновления позиции теста: ".$mysqli->error);
		}
	}
}

/**
 * Refreshes position of each task in 'subject' with specifyed tpid
 * @param int $tpid ID of 'subject'
 * @return void
 */
function refresh_task_positions($tpid){
	global $mysqli;

	$sql = "SELECT * FROM Tasks WHERE Tpid=$tpid ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время обновления позиции задания: ".$mysqli->error);  

	$pos = 10;

	while($row = $result->fetch_array()){
		$sql = "UPDATE Tasks SET Position=$pos WHERE PID={$row['PID']}";
		if(!$mysqli->query($sql)){
			my_die("Ошибка обновления позиции задания: ".$mysqli->error);
		}

		$pos += 10;
	}
}

/**
 * Returns 'select' with all values of one column of tests
 *
 * @param string $label label of 'select'
 * @param string $name name of 'select'
 * @param string $select the value name
 * @param string $selected Which variant will be selected by default
 * @return string
 */
function select_data($label, $name, $select, $selected){
	global $mysqli;
	$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY $select";
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	//SELECT
	$html =  "$label: <select name='$name'>";
	$pr = NULL;
	while($row = $result->fetch_array()){
		$cur = $row[$select];
		if($pr != $cur){
			$html .= "<option value = '$cur'";
			if($selected == $cur){
				$html .= " selected ";
			}
			$html .= ">$cur</option>";
			$pr = $cur;
		}
	}
	$html .= "</select><br>";
	return $html;
}

/**
 * Returns 'datalist' with all values of one column of tests
 *
 * @param string $label Label of 'datalist'
 * @param string $name Name of 'datalist'
 * @param string $select The value name
 * @param string $value Selected value
 * @return string
 */
function input_data_list($label, $name, $select, $value){
	global $mysqli;
	$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY $select";
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	//List
	$html =  "$label: <input name='$name' list='$name' value='$value'>
	<datalist id = '$name'>";
	$pr = NULL;
	while($row = $result->fetch_array()){
		$cur = $row[$select];
		if($pr != $cur){
			$html .= "<option value = '$cur'></option>";
			$pr = $cur;
		}
	}
	$html .= "</datalist><br>";
	return $html;
}

