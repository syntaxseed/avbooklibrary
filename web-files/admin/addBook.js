function validateForm(){

	var isOK = true;
	var value;
	var msg = "Please correct or supply the following:";

	//Reset message.
	document.getElementById("alertmsg").innerHTML = "";

	// Category
	value = document.getElementById("category").options[document.getElementById("category").selectedIndex].value;
	if(isNaN(value) || value >= 1000){
		msg += "<br />&nbsp;- Category.";
		isOK = false;
	}

	// Title
	value = document.getElementById("title").value;
	if(value == ""){
		msg += "<br />&nbsp;- Title.";
		isOK = false;
	}

	// Author
	value = document.getElementById("author").value;
	if(value == ""){
		msg += "<br />&nbsp;- Author.";
		isOK = false;
	}

	if(!isOK){
		msg = "<table style='border:1px solid #ff0000;background-color:#fff3f3;font-weight: bold; color: #ff0000;'><tr><td style='padding:2px;' cellpadding='0' align='left' valign='middle'>"+msg+"</td></tr></table><br />";
		document.getElementById("alertmsg").innerHTML = msg;
	}

	return isOK;

}

function trim(stringToTrim) {
	return stringToTrim.replace(/^\s+|\s+$/g,"");
}
