
var deleteData = document.getElementById("deleteAll");
var password = document.getElementById("pass");
var saveButton = document.getElementById("saveChanges");

//enable the Save Changes button
function update() {
    saveButton.disabled = false;
}

//Confirm with the user if they have checked the'Delete All Data' box
function confirmCheck() {
  if (deleteData.checked = true) {
    confirm('Are you sure you want to delete all data?');
  }
}
