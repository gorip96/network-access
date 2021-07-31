function validateContactForm() {

	var emailRegex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
	var userName = document.getElementById("userName").value;
	var userEmail = document.getElementById("userEmail").value;
	var subject = document.getElementById("subject").value;
	var content = document.getElementById("content").value;

	var valid = true;
	if (userName == "") {
		markAsInvalid("userName", "required");
		document.getElementById("userName").classList.add("error-field");
		valid = false;
	} else {
		markAsValid("userName");
		document.getElementById("userName").classList.remove("error-field");
	}

	if (userEmail == "") {
		markAsInvalid("userEmail", "required");
		document.getElementById("userEmail").classList.add("error-field");
		valid = false;
	} else if (!emailRegex.test(userEmail)) {
		markAsInvalid("userEmail", "invalid email address");
		document.getElementById("userEmail").classList.add("error-field");
		valid = false;
	} else {
		markAsValid("userEmail");
		document.getElementById("userEmail").classList.remove("error-field");
	}

	if (subject == "") {
		markAsInvalid("subject", "required");
		document.getElementById("subject").classList.add("error-field");
		valid = false;
	} else {
		markAsValid("subject");
		document.getElementById("subject").classList.remove("error-field");
	}
	if (content == "") {
		markAsInvalid("userMessage", "required");
		document.getElementById("content").classList.add("error-field");
		valid = false;
	} else {
		markAsValid("userMessage");
		document.getElementById("content").classList.remove("error-field");
	}

	return valid;
}

function markAsValid(id) {
	document.getElementById(id + "-info").style.display = "none";
}

function markAsInvalid(id, feedback) {
	document.getElementById(id + "-info").style.display = "inline";
	document.getElementById(id + "-info").innerText = feedback;
}
