//disable input for patient attributes unless checkbox is checked
var male = document.getElementById("male");
var female = document.getElementById("female");
var age = document.getElementById("age");
var option = document.getElementById("option");

function enable() {
  if (option.checked == true) {
    male.disabled = false;
    female.disabled = false;
    age.disabled = false
  } else {
    male.disabled = true;
    female.disabled = true;
    age.disabled = true;
  }
}
