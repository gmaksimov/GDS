<?php
include('header.php');

//Deletes Test with its Tasks and pictures
if(isset($_POST['Dpid'])){
  $Dpid = $_POST['Dpid'];
  //delete picture if it there's
  $sql = "SELECT * FROM Tasks WHERE tPID = '$Dpid'";
  $result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
  while($ind = $result->fetch_array()){
    $picture = $ind['Picture'];
    $taskpid = $ind['PID'];
    if(!$picture == ""){
      if(unlink("../$picture")){
        show_message("&#1050;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1072; &#1073;&#1099;&#1083;&#1072; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053;&#1040; &#1080;&#1079; &#1079;&#1072;&#1076;&#1072;&#1085;&#1080;&#1103; ".$taskpid, "ok");
      }else{
        show_message("&#1050;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1072; &#1053;&#1045; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053;&#1040; &#1080;&#1079; &#1079;&#1072;&#1076;&#1072;&#1085;&#1080;&#1103; $taskpid ".$mysqli->error, "error");
      }
    }else{
      show_message("&#1050;&#1072;&#1088;&#1090;&#1080;&#1085;&#1082;&#1072; &#1085;&#1077; &#1085;&#1072;&#1081;&#1076;&#1077;&#1085;&#1072; &#1074; &#1079;&#1072;&#1076;&#1072;&#1085;&#1080;&#1080; ".$taskpid, "ok");
    }
  }
  //delete tasks
  $sql = "Delete FROM Tasks WHERE tPID='$Dpid'";
  if($mysqli->query($sql)){
    show_message("&#1047;&#1072;&#1076;&#1072;&#1085;&#1080;&#1103; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053;&#1067;", "ok");
  }else{
    show_message("&#1047;&#1072;&#1076;&#1072;&#1085;&#1080;&#1103; &#1053;&#1045; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053;&#1067;".$mysqli->error, "error");
  }
  //delete test
  $sql = "Delete FROM Tests WHERE PID='$Dpid'";
  if($mysqli->query($sql)){
    show_message("&#1058;&#1077;&#1089;&#1090; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053;", "ok");
  }else{
    show_message("&#1058;&#1077;&#1089;&#1090; &#1053;&#1045; &#1059;&#1044;&#1040;&#1051;&#1045;&#1053; ".$mysqli->error, "error");
  }
}

//recovery of test
if(isset($_POST['Rpid'])){
  $Rpid = $_POST['Rpid'];
  $sql = "UPDATE Tests SET Deleted=0 WHERE PID='$Rpid'";
  if($mysqli->query($sql)){
    show_message("&#1058;&#1077;&#1089;&#1090; $Rpid &#1042;&#1054;&#1057;&#1057;&#1058;&#1040;&#1053;&#1054;&#1042;&#1051;&#1045;&#1053;", "ok");
  }else{
    show_message("&#1058;&#1077;&#1089;&#1090; $Rpid &#1053;&#1045; &#1042;&#1054;&#1057;&#1057;&#1058;&#1040;&#1053;&#1054;&#1042;&#1051;&#1045;&#1053; ".$mysqli->error, "error");
  }
}

show_message("&#1042;&#1053;&#1048;&#1052;&#1040;&#1053;&#1048;&#1045;! &#1045;&#1089;&#1083;&#1080; &#1074;&#1099; &#1085;&#1072;&#1078;&#1084;&#1077;&#1090;&#1077; &#1082;&#1085;&#1086;&#1087;&#1082;&#1091; &#1091;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100;, &#1090;&#1086; &#1059;&#1044;&#1040;&#1051;&#1048;&#1058;&#1045; &#1090;&#1077;&#1089;&#1090; &#1053;&#1040;&#1042;&#1057;&#1045;&#1043;&#1044;&#1040;!","error");
//Shows table
$sql = "SELECT * FROM Tests WHERE Deleted=1  ORDER BY Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
$table1 = "<table border=1 width='40%'><div class='caption_table'>";
$table2 = "</div><tr><th>Paper</th><th>Position</th><th>PID</th>
<th>&#1055;&#1088;&#1077;&#1076;&#1084;&#1077;&#1090;</th></tr>";
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
  /*if(!check_privilegies("-1")){
    continue; 
  }*/
  $cur_date = array($year, $halfyear, $grade, $booklet, $paper);
  if($cur_date != $date){
    if($table_started){
        echo "</table>";
    }
    $date = $cur_date;
    $date_string = "&#1075;&#1086;&#1076;: $year, &#1087;&#1086;&#1083;&#1091;&#1075;&#1086;&#1076;&#1080;&#1077;: $halfyear, &#1082;&#1083;&#1072;&#1089;&#1089;: $grade, &#1073;&#1091;&#1082;&#1083;&#1077;&#1090;: $booklet, Paper: $paper";
    echo $table1.$date_string.$table2;
    $table_started = true;
  }
  echo "<tr><td width=10>$paper</td><td width=10>$position</td><td width=10>$pid</td><td>$subject</td>
  <td width='10%'><form method=POST>
  <input type=text value=$pid name=Dpid hidden>
  <input type=submit value=&#1091;&#1076;&#1072;&#1083;&#1080;&#1090;&#1100;>
  </form></td>
  <td width='10%'><form method=POST>
  <input type=text value=$pid name=Rpid hidden>
  <input type=submit value=&#1074;&#1086;&#1089;&#1089;&#1090;&#1072;&#1085;&#1086;&#1074;&#1080;&#1090;&#1100;>
  </form></td>
  <td width='10%'><a href='../print3/print1.php?year=$year&halfyear=$halfyear&grade=$grade&booklet=$booklet&pid=$pid' target = '_blank'>&#1087;&#1077;&#1095;&#1072;&#1090;&#1072;&#1090;&#1100;</a></td>
  </tr>";
}
if($table_started){
    echo "</table>";
}
?>