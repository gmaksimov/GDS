<?php
require('header_req.php');

if(!isset($_GET['pid'])){
    my_die("Ошибка: Не дан PID");
}
if(!check_privilegies("-1")){
    my_die("У вас нет прав для просмотра этой странички, нужно -1");
}
$pid = addslashes($_GET['pid']);
$sql = "SELECT * FROM Tests WHERE PID='$pid'";
$tests = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
$row = $tests->fetch_array();
$year = $row['Year'];
$paper = $row['Paper'];
$halfyear = $row['Halfyear'];
$grade = $row['Grade'];
$booklet = $row['Booklet'];
//What subjects will be updated
if(isset($_POST['subjects']) && count($_POST['subjects']) != 0){
    for($i = 0; $i < count($_POST['subjects']); $i++){
        $sql = "SELECT * FROM Tests WHERE PID=".addslashes($_POST['subjects'][$i])." ORDER BY Position";
	$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
        $subjects[$i] = $result->fetch_array();
    }
}else{
    $sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Paper='$paper' AND Deleted=0 ORDER BY Position";
    $result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
    for($i = 0; $row = $result->fetch_array(); $i++){
        $subjects[$i] = $row;
    }
}
//make time equal with number of questions in each test
if(isset($_POST['time']) && $_POST['time'] == 1 && isset($_POST['variable'])){
    $variable = $_POST['variable'];
    for($i = 0; $i < count($subjects); $i++){
        $row = $subjects[$i];
	$tpid = $row['PID'];
        $sql2 = "SELECT * FROM Tasks WHERE Tpid='$tpid'";
        $result2 = $mysqli->query($sql2) OR my_die("Ошибка получения данных: ".$mysqli->error);
        $time = $result2->num_rows * $variable;
        $sql = "UPDATE Tests SET Time=$time WHERE PID = $tpid";
        if(!$mysqli->query($sql)){
            my_die("Ошибка изменения времени теста $tpid: ".$mysqli->error);
        }
    }
}

//copy subjects to booklet "B"
if(isset($_POST['Booklet_B']) && $_POST['Booklet_B'] == 1 && isset($_POST['booklet'])){
    $mybooklet = $_POST['booklet'];
    for($i = 0; $i < count($subjects); $i++){
    	$row = $subjects[$i];
        $subject = $row['Subject'];
        $otpid = $row['PID'];
        $sql = "INSERT INTO Tests (Year, Halfyear, Grade, Paper, Booklet, Subject, Position) VALUES ($year, $halfyear, $grade, '$paper', '$mybooklet', '$subject', 10050000000)";
        $result3 = $mysqli->query($sql) OR my_die("Ошибка копирования предметов в буклет B: ".$mysqli->error);
        $last_id = $mysqli->insert_id;
        if(isset($_POST['tasks']) && $_POST['tasks'] == 1){
            if(isset($_POST['rand']) && $_POST['rand'] == 1){
                $rd = "rand()";
            }else{
                $rd = "Position";
            }
            $sql = "SELECT * FROM Tasks WHERE Tpid=$otpid ORDER BY $rd";
            $result1 = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
            $pos = 0;
            while($test = $result1->fetch_array()){
                $pos++;
                $question = $test['Question'];
                $ans1 = $test['Ans1'];
                $ans2 = $test['Ans2'];
                $ans3 = $test['Ans3'];
                $ans4 = $test['Ans4'];
                $answer = $test['Answer'];
                $sql = "INSERT INTO Tasks (Question, Ans1, Ans2, Ans3, Ans4, Answer, Tpid, Position) VALUES ('$question', '$ans1', '$ans2', '$ans3', '$ans4', '$answer', $last_id, $pos)";
                if(!$mysqli->query($sql)){
                    my_die("Ошибка копирования заданий из $otpid ".$mysqli->error);
                }
            }
            refresh_task_positions($last_id);
        }
    }
    refresh_test_positions();
}

if(isset($_POST['rand_cur_tasks']) && $_POST['rand_cur_tasks'] != NULL){
	for($i = 0; $i < count($subjects); $i++){
	$row = $subjects[$i];
	$rd_pid = $row['PID'];
    $sql = "SELECT * FROM Tasks WHERE Tpid=$rd_pid ORDER BY rand()";
    $result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
    $new_pos = 0;
    while($row = $result->fetch_array()){
        $task_pid = $row['PID'];
        $sql = "UPDATE Tasks SET Position =$new_pos WHERE PID=$task_pid";
        if(!$mysqli->query($sql)){
            my_die("Error repositioning tasks, id $rd_pid: ".$mysqli->error);
        }
        $new_pos++;
    }
    refresh_task_positions($rd_pid);
}
}


if(isset($_POST['time']) || isset($_POST['Booklet_B']) || isset($_POST['rand_cur_tasks'])){
    header("Location: {$_SERVER['REQUEST_URI']}");
}

include ('header.php');


echo"
<h2>Опции:</h2>
<h2>Класс: $grade, Буклет: $booklet</h2>
<font size=3>Выберите предметы:</font>
<br>
<form method=POST>
<select name='subjects[]' multiple size=5>";
$sql = "SELECT * FROM Tests WHERE Year='$year' AND Grade='$grade' AND Booklet='$booklet' AND Halfyear='$halfyear' AND Paper='$paper' AND Deleted=0 ORDER BY Position";
$result = $mysqli->query($sql) OR my_die("Ошибка получения данных: ".$mysqli->error);
while($row = $result->fetch_array()){
    $sb = $row['Subject'];
    $tp = $row['PID'];
    echo "<option value='$tp'>$sb</option>";
}
echo"
</select><br>
<FONT size=3>Выберите действие(я):</font>
<br>
<input type=checkbox name=time value=1>
Время: количество заданий *
<input type=text name=variable placeholder='Число' value='' style='width: 40px' pattern='^[ 0-9]+$'>
<br>
<input type=checkbox name=rand_cur_tasks value=1>
Перемешать задания в текущем буклете
<br>
<br>
<input type=checkbox name=Booklet_B value=1>Копирование буклета:
<input type=text name=booklet style='width: 60px' placeholder='№/буква'>
<ul style='margin: 1px'>
    <li>
        (<span title='Иначе в скопированном тесте не будет заданий'>копировать задания</span>)<input type=checkbox name=tasks value=1>
    </li>
    <li>
        (перемешать задания в скопированном буклете)<input type=checkbox name=rand value=1>
    </li>
</ul>
<br>
<input type=submit value='Сохранить[s]' accesskey=s>
</form>";

include('footer.php');
