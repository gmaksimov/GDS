<?php
	include("header.php");
	if(isset($_POST['updates'])){
		$write = addslashes($_POST['updates']);
		$write = str_replace("\$date", date("[d.m.Y]"), $write);
		if(!file_put_contents("../updates.txt", $write)){
			my_die("Error saving updates");
		}
		header("Location: update.php");
	}
	if(isset($_POST['updated'])){
		$write = addslashes($_POST['updated']);
		$write = str_replace("\$date", date("[d.m.Y]"), $write);
		if(!file_put_contents("../updated.txt", $write)){
			my_die("Error saving updated");
		}
		header("Location: update.php");
	}
	//get txt
	$cols = 0;
	$cols2 = 0;
	if(!$updates = file_get_contents("../updates.txt")){
		$updates = "";
	}
	$update = explode("\r\n", $updates);
	foreach($update as $up){
		$cols = max(strlen($up), $cols);
	}
	if(!$updated = file_get_contents("../updated.txt")){
		$updated = "";
	}
	$updated = explode("\r\n", $updated);
	foreach($updated as $upd){
		$cols2 = max(strlen($upd), $cols2);
	}
?>
<form method=POST>
<p style="font: 15px bold">Here write what we will do:</p>
<textarea style="border: 3px double #6080f0; font: 20px Arial; min-width: 200px; min-height: 100px;" required rows = <?php echo count($update);?> cols = <?php echo $cols?> placeholder="updates" name=updates><?php
		foreach($update as $up){
			echo $up;
			if($up != $update[count($update) - 1]){
				echo "\n";
			}
		}
	?></textarea>
<p style="font: 15px bold">Here write what we have done:</p>
<textarea style="border: 3px double #6080f0; font: 20px Arial; min-width: 200px; min-height: 100px;" required rows = <?php echo count($updated);?> cols = <?php echo $cols2?> placeholder="what we've done" name=updated><?php
		foreach($updated as $upd){
			echo $upd;
			if($upd != $updated[count($updated) - 1]){
				echo "\n";
			}
		}
	?></textarea>
<input type=submit value=submit accesskey="s">
</form>
P.S. Use '$date' to write current date with format([d.m.Y.])
P.P.S. Use acceskey s to save
<?php
	include("footer.php");
?>