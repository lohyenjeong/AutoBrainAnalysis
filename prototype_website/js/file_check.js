/*Checks the size and type of a file when it is chosen,
  creating an alert if it does not have extension .nii.gz 
  or is larger than than 10 MB ?????? 
  Prevents Upload button being pressed until file is valid 
  size and type*/
//code adapted from http://forums.phpfreaks.com/topic/282302-applying-javascript-to-check-file-size-and-extension/
function checkFile(fieldObj)
{
    var uploadButton = document.getElementByID("uploader");

    var Name  = fieldObj.value;
    var Ext = Name.substr(Name.indexOf('.')+1);
    var Size = fieldObj.files[0].size;
    var SizeMB = (Size/10485760).toFixed(2);
    
    if (Ext != "nii.gz" || Size>10485760)
    {
        var error = "File type : " + Ext + "\n";
        error += "Size: " + SizeMB + " MB \n\n";
        error += "Please make sure your file is in the NIfTI format and is less than 10 MB.\n";
        alert(error);
        return false;
    }

    uploadButton.disabled = false;
    return true;
}


