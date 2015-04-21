<?php
require('header_req.php');
/* Set variables */
$question = addslashes($_POST['question']);
$ans1 = addslashes($_POST['ans1']);
$ans2 = addslashes($_POST['ans2']);
$ans3 = addslashes($_POST['ans3']);
$ans4 = addslashes($_POST['ans4']);
$answer = addslashes($_POST['answer']);
$tpid = addslashes($_POST['tpid']);
$pid = addslashes($_POST['pid']);
$position = addslashes($_POST['position']);
$fileimage = addslashes($_POST['fileimage']);

$sql = "SELECT Picture, Tpid FROM Tasks WHERE PID = $pid";
$result = $mysqli->query($sql) OR my_die($mysqli->error);
$row = $result->fetch_array();
$picture = $row['Picture'];

/* End set variables */
if(!check_privilegies($tpid)){
	my_die("У вас нет права доступа к этому заданию, нужно $tpid");
}
$sql = "SELECT Subject FROM Tests WHERE PID = $tpid";
$result = $mysqli->query($sql) OR my_die($mysqli->error, "error");
if($result->num_rows == 0){
	my_die("No test with pid $tpid");
}

//picture
//my_die($fileimage." ".$_FILES['file']);
if($fileimage == "del"){
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
do if(isset($_FILES['file']) &&  $fileimage == "new"){
	if(!check_privilegies($tpid)){
		my_die("У вас нет права доступа к этому заданию, нужно $tpid");
	}
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
	Position='$position',
	Tpid='$tpid'
	WHERE PID=$pid";
if (!$mysqli->query($sql)) {
	show_message("Ошибка сохранения вопроса: " . $mysqli->error, "error");
} else {
	//show_message("Вопрос обновлен. <a href='task_list.php?tpid=$tpid'>Goto task->tpid</a>", "ok");
	refresh_task_positions($tpid);
	if(isset($_POST['save_and_make'])){
		if(!check_privilegies($tpid)){
			my_die("У вас нет права доступа к этому заданию, нужно $tpid");
			return;
		}	
		$sql = "SELECT Position FROM Tasks WHERE Tpid=$tpid ORDER BY Position DESC";
		$result = $mysqli->query($sql) OR my_die("Failed where result: ".$mysqli->error);
		$sql = "INSERT INTO Tasks (Tpid, Position) VALUES ($tpid, 100500)";
		$result = $mysqli->query($sql) OR my_die("Error inserting: ".$mysqli->error);
		$last_id = $mysqli->insert_id;
		refresh_task_positions($tpid);
		header("Location: edit_task.php?pid=$last_id&s=1");
	} else {
		header("Location: task_list.php?tpid=$tpid");
	}
}

include('footer.php');