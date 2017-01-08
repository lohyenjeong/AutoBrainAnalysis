<?php
	defined ('_JEXEC') or die;

	require_once dirname(__FILE__).'/helper.php';

	echo "<br /> <h3> The following institutions are using MALPEM: </h3><br />";

	ModViewInstitutions::showRecords();

?>





