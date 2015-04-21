<?php
function show_message($msg, $type="info", $head = "", $time = 5000, $show_now = false){  
   // echo "<br>[<$type>$msg</$type>]<br>";
	
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

function my_die($message, $time = -1,$type = "error"){
    include_once('header.php');
	global $MSG;
	if(isset($message) && $message != NULL){
        global $MSG;
		echo "<br>[<$type>$message</$type>]<br>";
		if($time = -1){
			$time = 10000000;
		}
		$MSG[] = array('Ошибка', "$message", "$type", $time);
	}
    include_once('footer.php');
    die();
}

function select_test($name = "subject", $selected= -1){
  global $mysqli;
  $sql = "SELECT PID, Subject FROM Tests ORDER BY PID";
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
    echo "<option value='$pid'$mod>'$subject', $pid</option>";
  }
  echo "</select><br>";
}

function refresh_positions_dep2($tpid){
  global $mysqli;
  $sql = "SELECT PID FROM Tasks WHERE Tpid='$tpid' ORDER BY Position";
  $result = $mysqli->query($sql);
  $rows = $result->num_rows;
  for($i = 1; $i <= $rows; $i++){
    $row = $result->fetch_array() OR my_die("Ошибка получения данных: ".$mysqli->error);
    $pid = $row['PID'];
    $sql = "UPDATE Tasks SET Position='$i' WHERE PID='$pid'";
    if(!$mysqli->query($sql)){
      my_die("Ошибка записи данных: ".$mysqli->error);
    }
  }
  //show_message("Обновлено строк $rows в $tpid");
}

function select_numbers($name, $from, $to, $selected, $accesskey = "", $id = "", $accesskeys = array()){
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

function connect_to_mysql(){
  global $mysqli;
  require_once('../constants.php');
  global $_mysqli_user;
  global $_mysqli_pass;
  global $_mysqli_db;
  $mysqli = new mysqli("localhost","$_mysqli_user","$_mysqli_pass","$_mysqli_db");
  if(mysqli_connect_errno($mysqli)){
    my_die("Could not connect to MySql: ".$mysqli->error);
  } else {
    $mysqli->set_charset('utf8'); 
    //or maybe use {$mysqli->query('SET NAMES utf8') OR die(show_message("Error making utf8", "error"))} for utf8
  }
}

function check_privilegies($pr){
    require_once('session.php');
	global $mysqli;
    $pr = (int)$pr;
    $login = $_SESSION['gds']['login'];
  //show_message("Requested $pr for $login", "warning");
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
			echo"<option value='$zpid' selected>$i</option>";
		}else{
			echo"<option value='$zpid'>$i</option>";
		}
	}
}

function select_task_tpid($tpid, $selected){
    global $mysqli;
    $sql = "SELECT * FROM Tasks WHERE Tpid=$tpid ORDER BY Position";
    $result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время получения Tpid'а: ".$mysqli->error);
    $i = 1;
    while($row = $result->fetch_array()){
        if($row['PID'] != $selected){
            echo "<option value={$row['PID']}>$i</option>";
        } else {
            echo "<option value={$row['PID']} selected>$i</option>";
        }
        $i++;
    }
}


function refresh_test_positions(){
    global $mysqli;
    $sql = "SELECT * FROM Tests ORDER BY Year, Halfyear, Grade, Booklet, Paper, Position";
    $result = $mysqli->query($sql) OR my_die("Ошибка получения данных во время обноврения позиций тестов: ".$mysqli->error);
    $date = array();
    while($row = $result->fetch_array()){
        $cur_date = array($row['Year'],
            $row['Halfyear'], $row['Grade'],
            $row['Booklet'], $row['Paper']);
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

function input_data_list($label, $name, $select, $value){
global $mysqli;
$sql = "SELECT * FROM Tests WHERE Deleted=0 ORDER BY $select";
$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
//SELECT
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



