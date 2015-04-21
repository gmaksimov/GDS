<?php
$NO_TINYMCE = 0;
require('header_req.php');
include('header.php');

//update task
if(isset($_POST['question']) && isset($_POST['ans1']) && isset($_POST['ans2']) &&
   isset($_POST['ans3']) && isset($_POST['ans4']) && isset($_POST['answer']) &&
   isset($_POST['tpid']) && isset($_POST['pid']) && isset($_POST['fileimage'])){
	//vars
	$question = addslashes($_POST['question']);
	$ans1 = addslashes($_POST['ans1']);
	$ans2 = addslashes($_POST['ans2']);
	$ans3 = addslashes($_POST['ans3']);
	$ans4 = addslashes($_POST['ans4']);
	$answer = addslashes($_POST['answer']);
	$tpid = addslashes($_POST['tpid']);
	$pid = addslashes($_POST['pid']);
	$fileimage = addslashes($_POST['fileimage']);
	
	if($_POST['question'] == ""){
		my_die("No question given");
	}
	
	//create task
	if($pid == -1){
		if(!check_privilegies($tpid)){
			my_die("У вас нет права доступа к этому заданию, нужно $tpid");
			return;
		}	
		$sql = "SELECT Position FROM Tasks WHERE Tpid=$tpid ORDER BY Position DESC";
		$result = $mysqli->query($sql) OR my_die("Failed where result: ".$mysqli->error);
		$sql = "INSERT INTO Tasks (Tpid, Position) VALUES ($tpid, 100500)";
		$result = $mysqli->query($sql) OR my_die("Error inserting: ".$mysqli->error);
		$pid = $mysqli->insert_id;
		refresh_task_positions($tpid);
	}
	
	$sql = "SELECT Picture, Tpid FROM Tasks WHERE PID = $pid";
	$result = $mysqli->query($sql) OR my_die($mysqli->error);
	$row = $result->fetch_array();
	$picture = $row['Picture'];
	
	//check
	if(!check_privilegies($tpid)){
		my_die("У вас нет права доступа к этому заданию, нужно $tpid");
	}
	
	$sql = "SELECT Subject FROM Tests WHERE PID = $tpid";
	$result = $mysqli->query($sql) OR my_die($mysqli->error, "error");
	if($result->num_rows == 0){
		my_die("No test with pid $tpid");
	}
	
	//delete picture
	if($fileimage == "1"){
		if($picture && file_exists("pictures/".$picture)){ 
			unlink("pictures/".$picture);
		}
		$sql = "UPDATE Tasks SET Picture='' WHERE PID=$pid";
		if(!$mysqli->query($sql)){
			my_die($mysqli->error);
		}
		$picture="";
	}
	
	//if has picture
	do if(isset($_FILES['file']) &&  $fileimage == "1"){
		$allowedExts = array("gif", "jpeg", "jpg", "png");
		$path_info = pathinfo($_FILES['file']['name']);
		$extension  = $path_info['extension'];  
		$max_size = 3000000;//Bytes
		show_message("Size: ".$_FILES['file']['size']);
		if (!((($_FILES["file"]["type"] == "image/gif")
			|| ($_FILES["file"]["type"] == "image/jpeg")
			|| ($_FILES["file"]["type"] == "image/jpg")
			|| ($_FILES["file"]["type"] == "image/pjpeg")
			|| ($_FILES["file"]["type"] == "image/x-png")
			|| ($_FILES["file"]["type"] == "image/png")
			|| ($_FILES["file"]["type"] == "image/gif"))
			&& ($_FILES["file"]["size"] < $max_size)
			&& in_array($extension, $allowedExts))){
				show_message("Ошибка загрузки файла, возможно файл больше $max_size Байт.
					Картинка не загружена, но попробуем сохранить задание...");
				break;
		}
		if($_FILES['file']['error']){
			show_message("Ошибка, пожалуйста сообщите нам о ней: ".$_FILES['file']['error'].
						 "Картинка не загружена, но попробуем сохранить задание...", "error");
			break;
		}
		$filename = "pictues/".$picture;
		if($picture && file_exists("pictures/".$picture)){ 
			unlink($filename);
		}
		$new_path =  "pictures/$pid.$extension";
		move_uploaded_file($_FILES['file']['tmp_name'], $new_path);
		$sql = "UPDATE Tasks SET Picture = '$new_path' WHERE PID = $pid";
		if(!$mysqli->query($sql)){
			show_message("Ошибка изменения пути к новой картинке: ".$mysqli->error, "error");
		} else {
			//show_message("Адрес новой картинки: $new_path", "ok");
			$picture = $new_path;
		}
	} while(false);
	
	$sql = "UPDATE Tasks SET
		Question='$question',
		Ans1='$ans1',
		Ans2='$ans2',
		Ans3='$ans3',
		Ans4='$ans4',
		Answer='$answer',
		Tpid='$tpid'
		WHERE PID=$pid";
	if (!$mysqli->query($sql)) {
		show_message("Ошибка сохранения вопроса: " . $mysqli->error, "error");
	} else {
		//show_message("Вопрос обновлен. <a href='task_list.php?tpid=$tpid'>Goto task->tpid</a>", "ok");
		refresh_task_positions($tpid);
		if(isset($_POST['save_and_make'])){	
			header("Location: edit_task.php?new=$tpid");
		} else {
			header("Location: task_list.php?tpid=$tpid");
		}
	}
}

if(isset($_GET['new']) && $_GET['new'] != NULL){
	$tpid = addslashes($_GET['new']);
	$sql = "SELECT * FROM Tasks WHERE Tpid = $tpid";
	$result = $mysqli->query($sql);
	$new_pos = $result->num_rows + 1;
	$question = "";
	$ans1 = "";
	$ans2 = "";
	$ans3 = "";
	$ans4 = "";
	$answer = 1;
	$pid = -1;
	$position = -1;
	$filename = "new";
	$picture = false;
}else{
	if(!isset($_GET['pid']) || $_GET['pid'] == NULL){
		my_die("No task PID given");
	}
	
	$pid = $_GET['pid'];
	$sql = "SELECT * FROM Tasks WHERE PID=$pid";
	$result = $mysqli->query($sql) OR my_die("Error select task: ".$mysqli->error);
	$row = $result->fetch_array();

	$question = $row['Question'];
	$ans1 = $row['Ans1'];
	$ans2 = $row['Ans2'];
	$ans3 = $row['Ans3'];
	$ans4 = $row['Ans4'];
	$answer = $row['Answer'];
	$tpid = $row['Tpid'];
	$position = $row['Position'];
	$picture = $row['Picture'];
	$posit = $position / 10;
	
	$nxpos = $position + 10; // next pos of task
	$sql = "SELECT PID FROM Tasks WHERE Position = $nxpos AND Tpid = $tpid";
	$result = $mysqli->query($sql) OR my_die("Error ".$mysqli->error);
	$row = $result->fetch_array();
	$next_pid = $row['PID'];
	
	$prpos = $position - 10; // prev pos of task
	$sql = "SELECT PID FROM Tasks WHERE Position = $prpos AND Tpid = $tpid";
	$result = $mysqli->query($sql) OR my_die("Error ".$mysqli->error);
	$row = $result->fetch_array();
	$prev_pid = $row['PID'];
	
	if(!check_privilegies($tpid)){
		my_die("У вас нет прав доступа к этому заданию, нужно $pid");
	}
}
echo "<!--head of task-->
<div style='height: 60px'>";
if(isset($next_pid)){
	echo"<a href='edit_task.php?pid=$next_pid' accesskey='n'><div style='float: right; width: 10%; height: 60px; background-image: url(blue_arrow_right.png); background-size: auto 60px; background-repeat: no-repeat; background-position: center;'></div></a>";
}
echo "<a href=task_list.php?tpid=$tpid><div style='float: right; height: 60px; width: 80%; background-image: url(blue_arrow_top.png); background-size: auto 60px; background-repeat: no-repeat; background-position: center;'></div></a>";
if(isset($prev_pid)){
	echo"<a href='edit_task.php?pid=$prev_pid' accesskey='p'><div style='width: 10%; height: 60px; background-image: url(blue_arrow_left.png); background-size: auto 60px; background-repeat: no-repeat; background-position: center;'></div></a>";
}
echo"</div>
<!--body of task-->";
echo "<form method=POST name=form enctype='multipart/form-data'>";
if(isset($_GET['new'])){
	echo "Создание нового вопроса ($new_pos)";
}else{
	echo "Вопрос №$posit:";
}
echo"<br><textarea name=question autofocus rows=5 cols=70>$question</textarea><br>";
echo "
	<input type=text name=pid value=$pid hidden><div style='max-width: 49%'>
	Отв[1]: <input type=text name=ans1 placeholder=ans1 required value='$ans1' accesskey='1' class='ans'><br>
	Отв[2]: <input type=text name=ans2 placeholder=ans2 required value='$ans2' accesskey='2' class='ans'><br>
	Отв[3]: <input type=text name=ans3 placeholder=ans3 required value='$ans3' accesskey='3' class='ans'><br>
	Отв[4]: <input type=text name=ans4 placeholder=ans4 required value='$ans4' accesskey='4' class='ans'><br>
	Номер правильного: ";
select_numbers("answer", 1, 4, $answer, 'q');
echo "<input type=text value=$tpid name=tpid hidden></div>";
//picture
/*if($picture) {
	echo "<img src=$picture style='max-width: 300px; max-height: 300px'><br>";
}

<div class=image>
	<input type=radio id=save_picture  name='fileimage' value=del accesskey='r'>
  	<label for=save_picture>Удалить эту картинку[r]</label><br>
	<input type=radio id=change_picture name='fileimage' value=new checked accesskey='w'>
  	<label for=change_picture>Указать новую картинку или оставить старую [w]</label><br>
	<div id="file_input_container">	  
		<input type=file name='file' id=file accesskey='f'>
		<input type="button" onclick="clearFileInputField('file_input_container')" value="Очистить файл [e]" accesskey='e'>
	</div>
</div>*/
if($picture){
  echo "<br>
  <div class=image>
	Есть картинка: <br>
	<img src=$picture style='max-width: 300px; max-height: 300px;'><br>
	<input type=radio id=save_picture onchange='hide_file()' checked name='fileimage' value=0>
		<label for=save_picture>Оставить эту картинку</label>
	<input type=radio id=change_picture onchange='show_file()' name='fileimage' value=1>
		<label for=change_picture>Поменять/удалить</label><br>
	<div name='filediv' style='display: none' id='filediv'>
		<input type=file name='file'><br>
		(Чтобы удалить картинку, не выбирайте файл)
	</div>
	<br>
  </div>";
} else {
  echo "<br>
  <div class=image>Картинки нет: <br>
	  <input type=radio id=save_picture onchange='hide_file()' checked name='fileimage' value=0>
			<label for=save_picture>Не загружать картинку</label>
	  <input type=radio id=change_picture onchange='show_file()' name='fileimage' value=1>
			<label for=change_picture>Загрузить картинку</label>
	  <div name='filediv' style='display: none' id='filediv'>
			<input type=file name='file'>
	  </div>
	  <br>
  </div>";
}
?>
<br>
<input type=submit value='Сохранить [s]' name='just_save' accesskey='s'> <input type="submit" value='Сохранить и создать [q]' name='save_and_make' accesskey='q'><br>
</form>

<?php
include('footer.php');
?>
