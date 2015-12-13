<!DOCTYPE html>
<html>
<head>
<?php
/**
 * Prints header of the page (scripts and menu)
 */

if(!(isset($NO_TINYMCE) && $NO_TINYMCE == 1)){
	echo '<script type="text/javascript" src="../tinymce/js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
			tinymce.init({
				relative_urls : false,
				remove_script_host : false,
				document_base_url : "http://'.$_SERVER['SERVER_NAME'].'/'.$root_path.'",
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
<link rel=stylesheet href=../style.css type=text/css>
<link rel="stylesheet" href="../msg/css/jquery.notification.css">
<link rel="stylesheet" href="../login/style.css">
<script type="text/javascript" src="script.js"></script>
<script
	src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<title>GGds uControl</title>
</head>
<body>
<?php
if(!isset($_SESSION['ucontrol']['login']) || $_SESSION['ucontrol']['login'] == ""){
	return;
}
?>
	<form method=POST>
		<input type=submit value=Выйти name=unlogin>
	</form>
	<hr>
	<a href=user_list.php>Список юзеров</a> ||
	<a href=delete_result.php>Удалить тесты</a> ||
	<a href=..>GGds</a>
	<hr>