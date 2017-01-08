<?php
	defined ('_JEXEC') or die;

	require_once dirname(__FILE__).'/helper.php';

	/* check user is logged in */
	$user = JFactory::getUser();
	if ($user->guest) {
		echo "You must login to view your completed reports. <br />";
	}

	/* show or get files */
	else {
		$choosefile = $_GET["index"];

		if (!empty($choosefile))
			ModMalpemViewerHelper::showFile($choosefile);
		else	
			ModMalpemViewerHelper::getFiles();
	}

	echo "<br /> <br />
	<form name=\"submit\" method=\"post\" enctype=\"multipart/form-data\">
  <input type=\"submit\" name=\"submit\" value=\"Delete all files\">
	</form>";
	$input = new JInput;
	$post = $input->getArray($_POST);
	if ($post["submit"]) {
    ModMalpemViewerHelper::submit();
	}
?>





