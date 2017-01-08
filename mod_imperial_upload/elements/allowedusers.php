<?php

/**
* @version		1.2
* @copyright	Copyright (C) 2010-2011 Anders Wasén
* @license		GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

<script language="javascript" type="text/javascript">


function moveUDDuser() {
	var ol = document.getElementById('jformparamssettingidsuddsel');

	var os = ol.selectedIndex;
	if (os < 0) {
		alert('You have to select a user from the list first.');
		return false;
	}
	var userid = ol.options[os].text;
	
	var path = prompt('Give the path for the user defined directory for ' + userid + '.');
	
	var oli = document.getElementById('jformparamssettingidsudd');
	
	var val = userid + '>' + path;
	
	addOption(oli, val, val);
	
	var i = 0;
	var n = ol.options.length;
	for (i = 0; i < n; i++) {
		//oli.options[i].disabled = true;
		ol.options[i].selected = false;
	}
	
	selectAll(oli);
	
}

function selectAll(oe) {
	var i = 0;
	var n = oe.options.length;
	for (i = 1; i < n; i++) {
		//oli.options[i].disabled = true;
		oe.options[i].selected = true;
	}
}

function removeUDDuser() {
	var ol = document.getElementById('jformparamssettingidsudd');

	var os = ol.selectedIndex;
	if (os < 0) {
		alert('You have to select a user from the list first.');
		return false;
	}
	
	var ret = confirm('Are you sure you want to remove the user defined path for ' +ol.options[os].text + '?');
	
	if (ret) {
		deleteOption(ol, os);
	}
	
	selectAll(ol);
}

function addOption(theSel, theText, theValue)
{
  var newOpt = new Option(theText, theValue);
  var selLength = theSel.length;
  theSel.options[selLength] = newOpt;
}

function deleteOption(theSel, theIndex)
{ 
  var selLength = theSel.length;
  if(selLength>0)
  {
    theSel.options[theIndex] = null;
  }
}

//Make sure paths list is all selected!
var oapply = document.getElementById("toolbar-apply");
oapply.onmousedown = function() {
	selectAll(document.getElementById('jformparamssettingidsudd'));
}
var osave = document.getElementById("toolbar-save");
osave.onmousedown = function() {
	selectAll(document.getElementById('jformparamssettingidsudd'));
}



</script>

<?php

class JFormFieldAllowedUsers extends JFormField {
 
    protected $type = 'AllowedUsers'; 
		
	protected function getInput()
	{
		//JHTML::_('behavior.modal');
		$db = &JFactory::getDBO();

		// Get Module ID
		$mid = JRequest::getVar('id');
		if (strlen($mid) == 0) {
			$mid = JRequest::getVar('cid');
			if (is_array($mid)) $mid = $mid[0];
		}
		
		$name = (string)$this->element['name'];
		$control_name = 'jform[params]';
		$control_name_basic = 'jformparams';
		if (is_array($this->value))
			$value = $this->value;
		else	
			$value 	= (string)$this->value;

		
		$query = 'SELECT id AS value, username AS text'
 		. ' FROM #__users'
 		. ' WHERE block=0 ORDER BY name';

		$db->setQuery($query);
		$optionsAll[] = JHTML::_('select.option', "[ALL]", "[ALL]");
		$optionsDB = $db->loadObjectList();
		
		if ($name === 'settingidsund') {
			$options = array_merge($optionsAll, $optionsDB);
		} else {
			$options = $optionsDB;
		}
		
		$slistpath = '';

		$slistpath = JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]', 
			'class="inputbox" size="12" multiple="multiple"',
			'value', 'text', $value, $control_name_basic.$name);
		echo $slistpath;
		
		?>
		<!--
			<span class="input-append">
				<input type="hidden" readonly="readonly" disabled="disabled" value="" size="40" class="inputbox input-medium required" aria-required="true" required="required" />
				<a class="btn btn-primary" onclick="SqueezeBox.fromElement(this, {handler:'iframe', size: {x: 600, y: 450}, url:'/administrator/index.php?option=com_menus&amp;view=menutypes&amp;tmpl=component&amp;recordId=201'})">
						<i class="icon-list icon-white"></i> Select Users
				</a>
			</span>
		-->
		<?php

	}
	
		
}



?>