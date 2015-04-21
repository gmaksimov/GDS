function addpriv(pid){
	var priv = document.getElementById(pid);
	priv.className = "redtaskshow";
	priv.setAttribute("onclick", "deletepriv("+pid+")");
	var trpid = "tr" + pid;
	var tr = document.getElementById(trpid);
	tr.className = "taskrowred";
	var privs = document.getElementById("privs");
	var value = privs.value;
	var values = value.split(' ');
	var ok = 1;
	for(var i = 0; i < values.length; i++){
		if(pid == values[i]){
			ok = 0;
			break;
		}
	}
	if(ok == 1){
		value = value + " " + pid;
		privs.value = value;		
	}
}
function deletepriv(pid){
	var priv = document.getElementById(pid);
	priv.className = "bluetaskshow";
	priv.setAttribute("onclick", "addpriv("+pid+")");
	var trpid = "tr" + pid;
	var tr = document.getElementById(trpid);
	tr.className = "taskrow";
	var privs = document.getElementById("privs");
	var value = privs.value;
	var values = value.split(' ');
	var m = "";
	for(var i = 0; i < values.length; i++){
		if(pid != values[i]){
			if(i !== 0){
				m = m + " " + values[i];
			}else{
				m = values[i];
			}
		}
	}
	privs.value = m;
}