<!DOCTYPE html>
<html>
<head>
<?php
if(!(isset($NO_TINYMCE) && $NO_TINYMCE == 1)){
    echo '<script type="text/javascript" src="./tinymce/js/tinymce/tinymce.min.js"></script> 
		<script type="text/javascript">
			tinymce.init({
				relative_urls : false,
				remove_script_host : false,
				document_base_url : "http://gdsmaker.u2m.ru/",
				plugins : "table textcolor paste tiny_mce_wiris",
				paste_as_text: true,
				oninit : "setPlainText",
				selector : "textarea",
				language : "ru",
				toolbar1 : "bold italic underline | superscript subscript | bullist numlist",
				toolbar2 : "forecolor backcolor | tiny_mce_wiris_formulaEditor tiny_mce_wiris_CAS",
				menu : {
					table : {title : "Table", items : "inserttable tableprops deletetable | cell row column"}
				},
				statusbar : false
			});
			</script>';
}
?>
<meta charset=UTF-8>
<link rel=stylesheet href=style.css type=text/css>
<link rel="stylesheet" href="http://gdsmaker.u2m.ru/GDS_V2/msg/css/jquery.notification.css">
<link rel="stylesheet" href="login/style.css">
<script type="text/javascript" src="script.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<title>GGds</title>
</head>
	<body>
<?php
if(!isset($login) || $login == ""){
return;
}
echo "<strong>Ваш логин: </strong><font>$login</font>";
echo "<form method=POST><input type=submit value=Выйти name=unlogin></form><br>";
/*echo "<strong>&#1042;&#1072;&#1096;&#1080; &#1087;&#1088;&#1072;&#1074;&#1072;: </strong>";
$sql = "SELECT Privilegies FROM Users WHERE Login='$login'";
$result = $mysqli->query($sql) OR my_die("&#1053;&#1077; &#1089;&#1084;&#1086;&#1075; &#1074;&#1079;&#1103;&#1090;&#1100; &#1087;&#1088;&#1072;&#1074;&#1072;: ".$mysqli->error);
if($result->num_rows == 0){
  my_die("&#1057;&#1090;&#1088;&#1072;&#1085;&#1085;&#1086;, &#1085;&#1086; &#1091; &#1074;&#1072;&#1089; &#1085;&#1077;&#1090; &#1083;&#1086;&#1075;&#1080;&#1085;&#1072;");
}
$row = $result->fetch_array();
$privs = preg_split("/[\s,]+/", $row['Privilegies']);
$privs_string = "";
foreach($privs as $p){
  $privs_string .= "$p, ";
}
$privs_string = substr($privs_string, 0, -2);
echo $privs_string;*/
$sql = "SELECT * FROM Messages WHERE Addressee='$login'";
$result = $mysqli->query($sql) OR my_die("Error checking viewed messages: ".$mysqli->error);
$vcol = 0;
while($row = $result->fetch_array()){
	if($row['Viewed'] == 1){
		$vcol++;
/* 		$mes = substr($row['Message'], 0, 200);
 		$mes = substr_replace("<p>", "", $mes);
		$mes = substr_replace("</p>", "", $mes);
		$mes = substr_replace("<br>", "", $mes);
		$MSG[] = array('Новое сообщение', "от ".$row['Sender']." $mes", "info", 5000); */
	}
}
/* if($login == 'admin'){
	echo "<a href=test_list.php>Список тестов</a> || <a href=messages.php>сообщения"; if($vcol > 0){echo"<b>($vcol)</b>";} echo"</a> || <a href=new_message.php>новое сообщение</a>";// || <a href=send_mail.php>new e-mail message</a>";
}else{
	echo"<a href=test_list.php>Список тестов</a> || <a href=messages.php>сообщения"; if($vcol > 0){echo"<b>($vcol)</b>";} echo"</a>";
}
echo "|| <a href='help.php'>Помощь</a>";
echo"<hr>"; */
echo "
	<a href=test_list.php>Список тестов</a> || <a href=messages.php>сообщения";
if($vcol > 0){
	echo"<b>($vcol)</b>";
}
echo"</a>";
if(check_privilegies(-1)){
	echo" || 
		<a href=new_message.php>
			новое сообщение
		</a>";
}
echo"<hr>";

?>
