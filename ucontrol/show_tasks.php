<?php
//get privs
$upid = addslashes($_GET['pid']);
$sql = "SELECT Privilegies From Users WHERE PID=$upid";
$result = $mysqli->query($sql) OR my_die("Error getting privs".$mysqli->error);
$row = $result->fetch_array();
$privs = $row['Privilegies'];
//
$sql = "SELECT * FROM Tests WHERE Deleted=0  ORDER BY Year, Halfyear, Grade, Booklet, Position";
$result = $mysqli->query($sql) OR my_die("Error selecting all tasks: ".$mysqli->error);
echo"<div style = \"display:inline-table;\"><table border = '1'><tr><th><b>PID</b></th><th>year</th><th>halfyear</th><th>grade</th><th>booklet</th><th>paper</th><th>subject</th></tr>";
while($row = $result->fetch_array()){
  $pid = $row['PID'];
  $year = $row['Year'];
  $halfyear = $row['Halfyear'];
  $subject = $row['Subject'];
  $grade = $row['Grade'];
  $booklet = $row['Booklet'];
  $pid = $row['PID'];
  $position = $row['Position'];
  $paper = $row['Paper'];
  $red = 0;
  $privse = explode(' ', $privs);
  foreach($privse as $priv){
	  if($priv == $pid){
		  $red = 1;
		  break;
	  }	  
  }
  if($red == 1){
	  $href = "<a href=# class='redtaskshow' onclick='deletepriv($pid)' id=$pid>$pid</a>";
	  $class = "red";
  }else{
	  $href = "<a href=# class='bluetaskshow' onclick='addpriv($pid)' id=$pid>$pid</a>";
	  $class = "";
  }
  echo"<tr id='tr$pid' class='taskrow$class'><td><b>$href</b></td><td>$year</td><td>$halfyear</td><td>$grade</td><td>$booklet</td><td>$paper</td><td>$subject</td></tr>";
}
echo "</table></div>";
