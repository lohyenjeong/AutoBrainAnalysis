
//returns true if the email address is in the form "example@gmail.com" etc 
function validEmail(address) {
    //what counts as a valid email address?
    var regex = /[A-Z0-9._%+-]+@[A-Z0-9.-]+.[A-Z]{2,4}/igm; //http://www.sitepoint.com/javascript-validate-email-address-regex/
    return regex.test(address);
}

//returns true if the two passwords are identical
function samePassword(p1, p2) {
    if (p1 !== p2) {
        return false;
    }
    return true;
}

var submitButton = document.getElementById("submit");

//Function to check form when submit button is pressed.
//Will return true if email, 2 names, and password have been supplied,
//the email address is in a valid form and the password is at
//least 8 characters long.
var validate = function() {
    var fname = document.getElementById("fname").value;
    var sname = document.getElementById("sname").value;
    var email = document.getElementById("email").value;
    var pass1 = document.getElementById("pass1").value;
    var pass2 = document.getElementById("pass2").value;
    
    if (!validEmail(email)) {
        alert("Invalid email address format.");
        return false;
    }
                      
    if (!samePassword(pass1, pass2)) {
        alert("Passwords do not match.");
        return false;
    }
    
    if (pass1.length < 8) {
        alert("Password must be at least 8 characters long.");
        return false;
    }
    
    if (fname === "" || sname === "") {
        alert("Please fill in all required fields.");
        return false; 
    }  
    return true;
}

submitButton.addEventListener("click", validate);
