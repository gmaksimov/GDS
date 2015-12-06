
function clearFileInputField(Id) { 
  document.getElementById(Id).innerHTML = document.getElementById(Id).innerHTML; 
}

function mysave(Id){
	var input = document.getElementById(Id);
	var inner = input.value;
	input.removeAttribute("value");
	input.setAttribute("value", inner);
	//console.log("[ok] " + inner);
}

//edit_task.php (picture)
function show_file(){
	var filediv = document.getElementById('filediv');
	filediv.style.display = "inline";
}

//edit_task.php (picture)
function hide_file(){
	clearFileInputField('filediv');
	var filediv = document.getElementById('filediv');
	filediv.style.display = "none";
}

/*//for task answers update
function add_answer(){
	var anscol = document.getElementById('anscol').value;
	var Id = "ans" + anscol;
	
	document.getElementById(Id).removeAttribute("onclick");
	//document.getElementById(Id).setAttribute("required", "");
	document.getElementById(Id).removeAttribute("onfocusout");
	anscol = parseInt(anscol, 10) + 1;
	
	var prev = anscol - 1;
	
	//document.getElementById("ans" + prev).setAttribute("onchange", "mysave('ans" + prev + "')");
	
	var ansdiv = document.getElementById('ansdiv');
	var bf = ansdiv.innerHTML;
	
	ansdiv.innerHTML = bf + 
	"<div class='ansdivs' id='ansdiv" + anscol + "'><div class=texts style='display: inline' id=text" + anscol + ">ans[" + anscol + "]: </div><input type=text name=ans" + anscol + " placeholder=ans" + anscol + " id='ans" + anscol + 
	"' onclick=\"add_answer('ans" + anscol + "')\" value='' accesskey='" + anscol + "' class='ans' onchange = \"mysave('ans" + anscol + "')\"></div>";
	document.getElementById('anscol').value = anscol;
	
	var choose_ans = document.getElementById("choose_ans");
	bf = choose_ans.innerHTML;
	choose_ans.innerHTML = bf + "<option value='" + anscol + "'>" + anscol + "</option>";
}
*/
/*function change_answer_number(ansdiv, ans, text, NewId, fortrue = false){
	ansdiv.setAttribute("id", "ansdiv" + NewId);
	text.innetHTML = "ans["+ NewId +"]: ";
	text.setAttribute("id", "ans" + NewId);
	ans.setAttribute("name", "ans" + NewId);
	ans.setAttribute("placeholder", "ans" + NewId);
	ans.setAttribute("id", "ans" + NewId);
	if(fortrue){
		ans.setAttribute("onclick", "add_answer('ans" + NewId + "')");
	}
	ans.setAttribute("accesskey", NewId);
	ans.setAttribute("onchange", "mysave('ans" + NewId +"')");	
}

function remove_answer(Id){
	var answer = document.getElementById(Id);
	answer.remove();
	var answers = document.getElementsByClassName("ans");
	var ansdivs = document.getElementsByClassName("ansdivs");
	var texts = document.getElementsByClassName("texts");
	for(var i = 0; i <= answers.length; i++){
		var ans = answers[i];
		var ansdiv = ansdivs[i];
		var text = texts[i];
		i++;
		if(i == answers.length){
			var fortrue = true;
		}
		change_answer_number(ansdiv, ans, text, i, fortrue);
		i--;
	}
	console.log("ok");
}*/