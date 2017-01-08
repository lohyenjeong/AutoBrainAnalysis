<!-- Restrict access to PHP -->

<?php defined( '_JEXEC' ) or die( 'Restricted access' );?>

<!-- Include CSS, Javascript -->
<?php

$doc = JFactory::getDocument();

$doc->addStyleSheet('templates/' . $this->template . '/css/bootstrap.min.css');
$doc->addStyleSheet('templates/' . $this->template . '/css/custom.css');
$doc->addStyleSheet('templates/' . $this->template . '/css/custom.css');

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" 
   xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
	<head charset="utf-8">
		<jdoc:include type="head" />
		<meta name="viewport" content="width=device-width, initial-scale=1">
	</head>

	<body>
<!-- Navbar location: need to add logo here! Maybe have two locations: one for main menu, one for acct menu -->
	<nav class="navbar navbar-default navbar-fixed-top">	
		<div class="container-fluid">
			<div class="navbar-header">
				<!-- Navigation bar collapses on smaller screens -->
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
       				<span class="sr-only">Toggle navigation</span>
    		   		<span class="icon-bar"></span>
    		    	<span class="icon-bar"></span>
        			<span class="icon-bar"></span>
   			   </button>
   			   <!-- Logo -->
   			   <a href="index.php" class="pull-left"><img src="templates/<?php echo $this->template ?>/images/logo_malpem.jpg" alt="MALPEM" id="logo"> </a>
   			</div>

   			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<jdoc:include type="modules" name="header" />
			</div>
			<div class="navbar-right">
		</div>
	</nav>

<!-- Content -->
		<div class="container main-content">
			<div class="row">
				<div class="col-sm-4"> <jdoc:include type="modules" name="left-top" style="xhtml" /> </div>
				<div class="col-sm-4"> <jdoc:include type="modules" name="centre-top" style="xhtml" /> </div>
				<div class="col-sm-4"> <jdoc:include type="modules" name="right-top" style="xhtml" /> </div>
			</div>

			<div class="row">

				<div class="col-sm-8"> <jdoc:include type="component" /> </div>
				<div class="col-sm-4"> <jdoc:include type="modules" name="right-middle" style="xhtml" /> </div>
			</div>

			<div class="row">
					<div class="col-sm-12"> <jdoc:include type="message" /> </div>
			</div>

<!-- Footer -->
			<div class="container-fluid"> <jdoc:include type="modules" name="footer" style="xhtml" /> </div>

		</div>
    <script src="templates/<?php echo $this->template ?>/js/bootstrap.min.js"></script>
	</body>
</html>