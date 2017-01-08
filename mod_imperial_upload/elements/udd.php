<?php

/**
* @version		1.2
* @copyright	Copyright (C) 2010-2011 Anders Wasén
* @license		GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class JFormFieldUDD extends JFormField {
 
    protected $type = 'UDD'; 
		
	protected function getInput()
	{
	
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
		
		$options = array_merge($optionsAll, $optionsDB);
		

		$slist = '';
		$slist = JHTML::_('select.genericlist',  $optionsDB, 'jform[params][settingidsuddsel][]', 
			'class="inputbox" size="12"',
			'value', 'text', $value, 'jformparamssettingidsuddsel');
	//test
		$optionsPath[] = JHTML::_('select.option', '0', '[user defined directory paths]');
		
		// Get DB settings
		$udddblist = getBaseSettings($db, $mid);

		// Get rid of double quotes
		$udddblist = str_replace("\"", "", $udddblist);
		// Fix front-slashes
		$udddblist = str_replace("\/", "/", $udddblist);

		$tmp = "";
		$bracket = false;
		$uddlist = "";

		for ($i=0; $i<strlen($udddblist); $i++) {
			// There must be a smarter way to do this... but...
			$tmp = substr($udddblist, $i, 1);
			
			if ($tmp === "[") {
				$bracket = true;
				$tmp = "";
			}
			if ($tmp === "]") {
				$bracket = false;
				$tmp = "";
			}
			
			if ($bracket && $tmp === ",")
				$tmp = ";";
				
			$uddlist .= $tmp;
		}


//echo "uddlist=".$uddlist;

		$uddlist = explode(',', $uddlist);
	
		$optionsAddPath = '';
		foreach($uddlist as $val){

// "settingidsudd":["Anders>aaaa","Super User>super"]  should be cleaded as: settingidsudd:Anders>aaaa;Super User>super

			if (substr($val, 0, 14) === 'settingidsudd:') {

				$val = str_replace('settingidsudd:', '', $val);
				//$val = str_replace('"]', '', $val);

				//echo $value.'&';

				$uddsellist = explode(";", $val);
				foreach($uddsellist as $listval) {
					if ($listval != '0') {
					$optionsAddPath[] = JHTML::_('select.option', $listval, $listval);
					}
				}
				
			}
		}
		
		if (is_array($optionsAddPath)) {
			$optionsPath = array_merge($optionsPath, $optionsAddPath);
		}
		
		$slistpath = '';
		/*$slistpath = JHTML::_('select.genericlist', $optionsPath, 'params[settingidsuddpath][]', 
			'class="inputbox" size="12" multiple="multiple"',
			'value', 'text', $value, 'paramssettingidsuddpath');
		*/
		$slistpath = JHTML::_('select.genericlist', $optionsPath, 'jform[params][settingidsudd][]', 
			'class="inputbox" size="12" multiple="multiple"',
			'value', 'text', $value, 'jformparamssettingidsudd');
		
		return setUDDhtml($slist, $slistpath);

	}	
		
}

function getBaseSettings($db, $mid) {
		$udddblist = '';
		$query = 'SELECT params AS value'
		. ' FROM #__modules where id=' . $mid;
		$db->setQuery($query);
		$dblist = $db->loadObjectList();
		// Parameter list is last in array
		//$udddblist = $dblist[count($dblist)-1]->value;
		// Above not always true, make sure to search all params in dblist!!!!
		$cnt = 0;
		do {
			$udddblist = $dblist[$cnt]->value;
	//echo "udddblist$cnt=".$udddblist."(".strrpos($udddblist, "upload_location").")<br/>";
			if (strrpos($udddblist, "upload_location") >= 1) {
				//echo "FOUND IT!";
				break;
			}
			$cnt = $cnt + 1;
		} while (count($dblist) > $cnt);
		
		return $udddblist;
	}

	function setUDDhtml($sin, $sin2) {
		$shtml = '';

		//$shtml .= '<table border=0><tr><td>';
		$shtml .= $sin;
		//$shtml .= '</td><td><input type="button" value=">>" onclick="javascript: moveUDDuser();"/><br /><input type="button" value="<<" onclick="javascript: removeUDDuser();" /></td><td>';
		$shtml .= '<input type="button" value=">>" onclick="javascript: moveUDDuser();"/><input type="button" value="<<" onclick="javascript: removeUDDuser();" />';
		$shtml .= $sin2;
		//$shtml .= '</td></tr></table>';
		
		return $shtml;
	}



?>