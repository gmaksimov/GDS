<?php
$file = ($_SERVER['DOCUMENT_ROOT']."/../constants.txt");
$f = file($file);
for($i = 0; $i < count($f); $i++){
	$var = explode("=", $f[$i]);
	global ${$var[0]};
	${$var[0]} = $var[1];
}
?>
