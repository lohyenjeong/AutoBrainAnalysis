<?php
	class ModViewInstitutions {
		public static function showRecords() {

			$db = JFactory::getDbo();
			$region = "profile.region";

			$query = $db->getQuery(true)
						->select("DISTINCT profile_value")
						->from($db->quoteName('r17ie_user_profiles'))
						->where($db->quoteName('profile_key')."=".$db->quote($region))
						->where($db->quoteName('profile_key')."!=".$db->quote(""));

			$db->setQuery($query);
			$rows = $db->loadRowList();

			/* No users in database */
			if ($rows <=0)
				echo "No institutions listed yet <br />";

			/* One or more users */
			else {
				$index = 1;
				echo "<h4>";

				/* Repeatedly get information for each user */
				foreach ($rows as $row) {
					$institutions = $row[0];
					$better = substr($institutions,1,-1);


					echo $better."<br />";

					$index++;
				}
				echo "</h4>";

			}	

		}
	}

?>
