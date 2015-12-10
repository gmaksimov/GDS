<!DOCTYPE html>
<html>
<head>
<?php
/**
 * Prints header of the page (scripts and menu)
 */

if(!(isset($NO_TINYMCE) && $NO_TINYMCE == 1)){
	echo '<script type="text/javascript" src="./tinymce/js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
			tinymce.init({
				relative_urls : false,
				remove_script_host : false,
				document_base_url : "http://'.$_SERVER['SERVER_NAME'].'/",
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
<link rel="stylesheet"
	href="./msg/css/jquery.notification.css">
<link rel="stylesheet" href="login/style.css">
<script type="text/javascript" src="script.js"></script>
<script
	src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<title>GGds</title>
</head>
<body>
<?php
if(!isset($login) || $login == ""){
	return;
}
echo "
<strong>Ваш логин: </strong>
<font>$login</font>
<form method=POST>
	<input type=submit value=Выйти name=unlogin>
</form>
<br>";
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