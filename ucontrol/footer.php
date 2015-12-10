<?php
/**
 * This page is for making correct footer and showing pop up messages
 */
global $MSG;
global $root_path;
echo "
<!--footer-->
";

//for showing pop up messages
echo "
<script src='http://".$_SERVER['SERVER_NAME']."/$root_path/msg/lib/jquery.min.js'></script>
<script src='http://".$_SERVER['SERVER_NAME']."/$root_path/msg/js/jquery.notification.min.js'></script>
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
