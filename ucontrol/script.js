/**
 * This page contains scripts, that is required in ucontrol
 * 
 * Warning! This scripts coult make browsing slowlier (if add too muck privs (All ~250 checks on 41 subjects))
 */

/*
 * For privs group work
 */
function multipriv() {

	// get field's pid & task
	var pid = arguments[0];
	var task = arguments[1];

	// change function's task & field's class to opposite
	var field = document.getElementById(pid);
	if (task == "add") {
		task = "delete";
		field.className = "redtaskshow";
	} else {
		task = "add";
		field.className = "bluetaskshow";
	}

	// prepare new onclick value
	var onclick = "multipriv('" + pid + "', '" + task + "'";

	// add or delete privs & add pid to new onclick value
	for ( var i = 2; i < arguments.length; i++) {
		var argument = arguments[i];
		onclick += ", " + argument;

		// we reversed this value above
		if (task == "add") {
			deletepriv(argument);
		} else {
			addpriv(argument);
		}
	}
	onclick += ")";

	// save new onclick
	field.setAttribute("onclick", onclick);
}

/*
 * Getting throw privs of (row|column|test) and checking if the all selected
 * 
 *  if so, return true, false otherwise
 */
function get_throw(pids){
	for(var i = 2; i < pids.length; i++){
		if(document.getElementById(pids[i]).className != "redtaskshow"){
			return false;
		}
	}
	return true;
}

/*
 * Change field's style & onclick value (privs)
 * 
 * f.e. If you selected row, should be changed value of booklet or grade  
 */
function check(pid, task) {
	if (task == "delete") {
		set_link_view(pid, task);
		return;
	}
	var field = document.getElementById(pid);
	var pids = field.getAttribute("onclick").replace(")", "").split("(")[1].split(', ');
	if(get_throw(pids)){
		set_link_view(pid, "add");
	}else{
		set_link_view(pid, "delete");
	}
}

/*
 * Sets field's class & style depending on task
 * 
 * Should be use only with 'multi' fields!
 */
function set_link_view(pid, task) {
	var field = document.getElementById(pid);
	if (task == "add") {
		field.className = "redtaskshow";
	} else {
		field.className = "bluetaskshow";
	}
	var attrs = field.getAttribute("onclick").split(', ');
	var newattr = attrs[0] + ", ";
	if (task == "add") {
		newattr += "'delete'";
	} else {
		newattr += "'add'";
	}
	for ( var i = 2; i < attrs.length; i++) {
		newattr += ", " + attrs[i];
	}
	field.setAttribute("onclick", newattr);
}

/*
 * Get right values of column, row, grade & call check & set style and onclick.
 */
function initiate_check(pid, task, column, row, grade){
	var priv = document.getElementById(pid);
	if (column == undefined) {
		column = priv.getAttribute("onclick").split(', ')[1];
	}
	if (row == undefined) {
		row = priv.getAttribute("onclick").split(', ')[2];
	}
	if (grade == undefined) {
		grade = priv.getAttribute("onclick").split(', ')[3].replace(')', "");
	}
	if(task == "add"){
		priv.className = "redtaskshow";
		var reversed_task = "delete";
	}else{
		priv.className = "bluetaskshow";
		var reversed_task = "add";
	}
	check("Booklet" + column, task);
	check("Grade" + grade, task);
	check("Subjects" + row, task);
	priv.setAttribute("onclick", reversed_task + "priv(" + pid + ", " + column + ", " + row + ", " + grade + ")");
}

/*
 * Add priv to form & change value and style of link
 */
function addpriv(pid, column, row, grade) {
	initiate_check(pid, "add", column, row, grade);
	var trpid = "tr" + pid;
	var tr = document.getElementById(trpid);
	tr.className = "taskrowred";
	var privs = document.getElementById("privs");
	var value = privs.value;
	var values = value.split(' ');
	var ok = 1;
	for ( var i = 0; i < values.length; i++) {
		if (pid == values[i]) {
			ok = 0;
			break;
		}
	}
	if (ok == 1) {
		value = value + " " + pid;
		privs.value = value;
	}
}

/*
 * Delete priv to form & change value and style of link
 */
function deletepriv(pid, column, row, grade) {
	initiate_check(pid, "delete", column, row, grade);
	var trpid = "tr" + pid;
	var tr = document.getElementById(trpid);
	tr.className = "taskrow";
	var privs = document.getElementById("privs");
	var value = privs.value;
	var values = value.split(' ');
	var m = "";
	for ( var i = 0; i < values.length; i++) {
		if (pid != values[i]) {
			if (i !== 0) {
				m = m + " " + values[i];
			} else {
				m = values[i];
			}
		}
	}
	privs.value = m;
}

/*
 * Reverses field's class & style
 * 
 * Did used anywhere
 */
function reverse_link_view(pid) {
	var field = document.getElementById(pid);
	if (field.className == "redtaskshow") {
		field.className = "bluetaskshow";
	} else if (field.className == "bluetaskshow") {
		field.className = "redtaskshow";
	}
	var onclick = field.getAttribute("onclick").substr(0, 3)
	if (onclick == "del") {
		field.setAttribute("onclick", "addpriv(" + pid + ")");
	} else if (onclick == "add") {
		field.setAttribute("onclick", "deletepriv(" + pid + ")");
	} else if (onclick == "mul") {
		var attrs = field.getAttribute("onclick").split(', ');
		var newattr = attrs[0] + ", ";
		if (attrs[1].substr(1, 3) == "add") {
			newattr += "'delete'";
		} else {
			newattr += "'add'";
		}
		for ( var i = 2; i < attrs.length; i++) {
			newattr += ", " + attrs[i];
		}
		field.setAttribute("onclick", newattr);
	}
}
