<?php
$NO_TINYMCE = 0;
require('header_req.php');
include('header.php');

//update task
if(isset($_POST['question']) && isset($_POST['anscol']) && isset($_POST['answer']) &&
   isset($_POST['tpid']) && isset($_POST['pid']) && isset($_POST['fileimage'])){
//vars
	$question = addslashes($_POST['question']);
	$anscol = addslashes($_POST['anscol']);
	$answer = addslashes($_POST['answer']);
	$tpid = addslashes($_POST['tpid']);
	$pid = addslashes($_POST['pid']);
	$fileimage = addslashes($_POST['fileimage']);
	
	//get ans
	$realans = 0;
	for($i = 1; $i <= $anscol; $i++){
		$a = "ans$i";
		if($_POST[$a] != ""){
			$ans[$i] = addslashes($_POST[$a]);
			$realans++;
		}
	}
	//get real count of answers
	$anscol = $realans;
	
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
	
//update answers
	$sql = "SELECT * FROM Answers WHERE Tid = $pid";
	$result = $mysqli->query($sql) OR my_die($mysqli->error);
	$old_anscol = $result->num_rows;

	//delete useless answers
	if($anscol < $old_anscol){
		$sql = "SELECT PID FROM Answers WHERE Tid=$pid";
		$result = $mysqli->query($sql) OR my_die($mysqli->error);
		while($row = $result->fetch_array()){
			$apid = $row['PID'];
			$sql = "DELETE FROM Answers WHERE PID=$apid";
			if(!$mysqli->query($sql)){
				my_die("Error updating answers(delete): ".$mysqli->error);
			}
			$old_anscol--;
			if($anscol == $old_anscol){
				break;
			}
		}
	}else if($anscol > $old_anscol){	//create missing answers
		while($anscol > $old_anscol){
			$sql = "INSERT INTO Answers (Tid) VALUES ($pid)";
			if(!$mysqli->query($sql)){
				my_die("Error updating answers(create): ".$mysqli->error);
			}
			$old_anscol++;
		}
	}
	
	//ans updating
	if($anscol == $old_anscol){
		$sql = "SELECT * FROM Answers WHERE Tid=$pid";
		$result = $mysqli->query($sql) OR my_die($mysqli->error);
		for($i = 1; $row = $result->fetch_array(); $i++){
			$apid  = $row['PID'];
			$a = $ans[$i];
			$sql = "UPDATE Answers SET Answer = '$a', Position='$i' WHERE PID = '$apid'";
			if(!$mysqli->query($sql)){
				my_die("Error updating answers (update)[apid = $apid, pid =  $pid]: ".$mysqli->error);
			}
		}
	}else{	//could not be
		my_die("Anscol = $anscol != Old_anscol = $old_anscol");
	}
	
//data update
	$sql = "UPDATE Tasks SET
		Question='$question',
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

//main html
if(isset($_GET['new']) && $_GET['new'] != NULL){
	$tpid = addslashes($_GET['new']);
	$question = "";
	$answer = 1;
	$pid = -1;
	$position = -1;
	$filename = "new";
	$picture = false;
	$anscol = 4;
}else{
	if(!isset($_GET['pid']) || $_GET['pid'] == NULL){
		my_die("No task PID given");
	}
	
	$pid = $_GET['pid'];
	$sql = "SELECT * FROM Tasks WHERE PID=$pid";
	$result = $mysqli->query($sql) OR my_die("Error select task: ".$mysqli->error);
	$row = $result->fetch_array();

	$question = $row['Question'];
	$answer = $row['Answer'];
	$tpid = $row['Tpid'];
	$position = $row['Position'];
	$picture = $row['Picture'];
	$posit = $position / 10;
	
	$sql = "SELECT * FROM Answers WHERE Tid=$pid";
	$result = $mysqli->query($sql) OR my_die("Error: ".$mysqli->error);
	$anscol = $result->num_rows;
	
	if(!check_privilegies($tpid)){
		my_die("У вас нет прав доступа к этому заданию, нужно $pid");
	}
}
echo "<h2><a href=task_list.php?tpid=$tpid>К списку заданий</a></h2>
	<form method=POST name=form enctype='multipart/form-data'>";
if(isset($_GET['new'])){
	echo "Создание нового вопроса";
}else{
	echo "Вопрос №$posit:";
}
echo "<br><textarea name=question autofocus rows=5 cols=70>$question</textarea><br>
	<input type=text name=pid value=$pid hidden><div style='max-width: 49%'><div id=ansdiv>";
//answers
if($pid == -1){
	echo"<div class='ansdivs' id='ansdiv1'><div class=texts style='display: inline' id=text1>ans[1]: </div>
	<input type=text name=ans1 placeholder=ans1 id='ans1' value='' accesskey='1' class='ans' onchange = \"mysave('ans1')\"></div>
	<div class='ansdivs' id='ansdiv2'><div class=texts style='display: inline' id=text2>ans[2]: </div>
	<input type=text name=ans2 placeholder=ans2 id='ans2' value='' accesskey='2' class='ans' onchange = \"mysave('ans2')\"></div>
	<div class='ansdivs' id='ansdiv3'><div class=texts style='display: inline' id=text3>ans[3]: </div>
	<input type=text name=ans3 placeholder=ans3 id='ans3' value='' accesskey='3' class='ans' onchange = \"mysave('ans3')\"></div>
	<div class='ansdivs' id='ansdiv4'><div class=texts style='display: inline' id=text4>ans[4]: </div>
	<input type=text name=ans4 placeholder=ans4 id='ans4' value='' onclick=\"add_answer('ans4')\" accesskey='4' class='ans' onchange = \"mysave('ans4')\"></div>";
}else{
	$sql = "SELECT * FROM Answers WHERE Tid = $pid ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die($mysqli->error);
	for($i = 1; $row = $result->fetch_array(); $i++){
		$ans = $row['Answer'];
		if($i == $anscol){
			$last_ans = "onclick='add_answer()'";
		}else{
			$last_ans = "";
		}
		echo"<div class='ansdivs' id='ansdiv$i'><div class=texts style='display: inline' id=text$i>ans[$i]: </div>
		<input type=text name=ans$i placeholder=ans$i id='ans$i' onclick=\"add_answer('ans$i')\" value='$ans' accesskey='$i' class='ans' onchange = \"mysave('ans$i')\"></div>";
		//echo "<div class='ansdivs$i' id='ansdiv$i'>ans[$i]: <input type=text name=ans$i placeholder=ans$i  id='ans$i' value='$ans' accesskey='$i' class='ans' onchange=\"mysave('ans$i')\" $last_ans></div>";
	}
}
echo "
		<input type=text id=anscol name=anscol value=$anscol hidden>
		</div>
		<button type=button onclick='add_answer()'>
			Add
		</button>
	Номер правильного: ";
select_numbers("answer", 1, 4, $answer, 'q', "choose_ans");
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
<input type=submit value='Сохранить [s]' name='just_save' accesskey='s'> <input type="submit" value='Сохранить и создать [d]' name='save_and_make' accesskey='d'><br>
</form>

<?php
include('footer.php');
?>