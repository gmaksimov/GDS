function clearFileInputField(Id) { 
  document.getElementById(Id).innerHTML = document.getElementById(Id).innerHTML; 
}

var wind;
var ee;
window.onselect = selectText;
window.onload = function() {
	text_editor("myclass1");
	//wiris_text_editor();
};
/*
function wiris_text_editor(){
	var forms = document.getElementsByTagName("textarea");
	for(var i = 0; i < forms.length; i++){
		var form = forms[i];
		var inner = form.innerHTML;
		var parent = form.parentNode;
		parent.removeChild(form);
		//parent
	}
}*/

function mysave(Id){
	var input = document.getElementById(Id);
	var inner = input.value;
	input.removeAttribute("value");
	input.setAttribute("value", inner);
	console.log("[ok] " + inner);
}

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

function open_pict(){
  var pid = document.picture.ppid.value;
  var a = "task_picture.php?pid="+pid;
  wind = window.open(a, "display_pict",
    "width=600,height=600,status=no,toolbar=no,menubar=no");
}

function close_pict() {
  window.close();
}

function show_file(){
	var filediv = document.getElementById('filediv');
	filediv.style.display = "inline";
}

function hide_file(){
	clearFileInputField('filediv');
	var filediv = document.getElementById('filediv');
	filediv.style.display = "none";
}

/* function catched(pid){
	var address = "take_pic_adress.php?pid=" + pid;
	var fromSite = $.ajax({url: address
	}).done(function(){
		alert("done");	
		var edit = document.getElementById("image-div");
       	edit.innerHTML = "";
		var ht = "<img src='" + msg;
		edit.innerHTML = ht + "'>";	
	}).fail(function(){
		alert("ajax error");
	});
}
 */
function text_editor(selector){
	var forms = document.getElementsByClassName(selector);
	for(var i = 0; i < forms.length; i++){
		var form = forms[i];
		var inner = form.innerHTML;
		var buttons = "<button onclick='add_tag(\"i\")'>i</button>";
		var buttonsend = "";
		form.innerHTML = buttons + inner + buttonsend;
	}
}

function add_tag(tag){
	//alert('1');
	start = ee.target.selectionStart;
    end = ee.target.selectionEnd;
	check(tag);
	bt = ee.target.value.substring(start, end);
	bf = ee.target.value.substring(0, start);
	af = ee.target.value.substring(end);
	var text = bf + "<" + tag + ">" + bt + "</" + tag + ">" + af;
	ee.target.value = text;
}

function selectText(e){
	ee = e;
    //var text = e.target.value.substring(start, end);
}

function change(start, end, bt){
	var bf, af;
	bf = ee.target.value.substring(0, start);
	af = ee.target.value.substring(start, end);
	ee.target.value = bf + bt + af;
	return start + bt;
}

function ifopentag(start, tag){
	if(ee.target.value[start] == "<" && ee.target.value.substring(start + 1, tag.length) == tag && ee.target.value[start + 1 + tag.length + 1] == ">"){
		return 1;
	}
	return 0;
}

function ifclosetag(start, tag){
	if(ee.target.value[start] == "<" && ee.target.value[start + 1] == "/" && ee.target.value.substring(start + 2, tag.length) == tag && ee.target.value[start + 2 + tag.length + 1] == ">"){
		return 1;
	}
	return 0;
}

function check(tag){
	var open = 0;
	var close = 0;
	for(var i = 0; i < ee.target.value.length; i++){
		if(ifopentag(i, tag)){
			if(open == 1){
				i = change(i, i + 2 + tag.length, "</" + tag + ">");
			}
		}else if(ifclosetag(i, tag)){
			if(open === 0){
				i = change(i, i + 3 + tag.length, "");
			}
		}
	}
}
/*function check(tag){
	var open = 0;
	var close = 0;
	var bf, bt, af, l;
	for(var i = 0; i < ee.target.value.length; i++){
		if(ee.target.value[i] == '<'){
			for(var j = 0; j < tag.length; j++){
				if(ee.target.value[i+j] != tag[j]){
					break;
				}
				if(j == tag.length - 1 && ee.target.value[i+j+1] == ">"){
					if(open !== 0){
						bf = ee.target.value.substring(0, i - 1);
						af = ee.target.value.substring(i + j + 2,  ee.target.value);
						bt = "</" + tag + ">";
						ee.target.value = bf + bt + af;
						i += j + 1;
						open = 0;
						close++;
					}else{
						open = 1;
					}
				}
			}
		}else if(ee.target.value[i] == "<" && ee.target.value[i+1] == "/"){
			if(open != 1 && close === 0){
				for(var c = 0; c < tag.length; c++){
					if(ee.target.value[i+c] != tag[c]){
						break;
					}
					if(c == tag.lebgth - 1 && ee.target.value[i+c+1] == ">"){
						bf = ee.target.value.substring(0, i - 1);
						af = ee.target.value.substring(i + c + 2,  ee.target.value);
						ee.target.value = bf + af;
						i--;
					}
				}
			}else if(open != 1 && close !== 0){
				for(var z = 0; z < tag.length; z++){
					if(ee.target.value[i+z] != tag[z]){
						break;
					}
					if(z == tag.length - 1 && ee.target.value[i+z+1] == ">"){
						bf = ee.target.value.substring(0, i - 1);
						af = ee.target.value.substring(i + z + 2,  ee.target.value);
						bt = "</" + tag + ">";
						ee.target.value = bf + bt + af;
						i += z + 2;
						open = 1;
						close--;
					}
				}
			}else if(open == 1 && close == 1){
				close = 0;
				open = 0;
			}
		}
	}
}*/