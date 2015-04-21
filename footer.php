<?php
	if(isset($_SESSION['gds']['login']) && $_SESSION['gds'] != NULL){
		echo"<center><br><hr><a href='tech_help.php'>Техподдержка</a></center>";
	}
echo "<script src='http://gdsmaker.u2m.ru/GDS_V2/msg/lib/jquery.min.js'></script>
<script src='http://gdsmaker.u2m.ru/GDS_V2/msg/js/jquery.notification.min.js'></script>
<script type='text/javascript'>
$( document ).ready(function() {";
for($i = 0; $i < count($MSG); $i++){
	$MSG_title = $MSG[$i][0];
	$MSG_text = $MSG[$i][1];
	$MSG_type = $MSG[$i][2];
	$MSG_time = $MSG[$i][3];
	echo"
		$.notify('$MSG_title', '$MSG_text', '$MSG_type', $MSG_time);
	";
}
 echo"});
</script>";
?>
</body>
</html>