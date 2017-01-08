<?php
	class ModMalpemAdminView {
		public static function showRecords() {

			/* set up database query to get job summary */
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			echo "Welcome to the administrator view, ".$user->name.".<br /><br />";

			echo "<h4> Summary: </h4> <br />";
			$query_null = "SELECT COUNT(job_id) AS count FROM r17ie_user_jobs WHERE output_path IS NULL";
			$query_notnull = "SELECT COUNT(job_id) AS count FROM r17ie_user_jobs WHERE output_path IS NOT NULL";
			$db->setQuery( $query_null ); 
			$query_processing = $db->loadResult();
			$db->setQuery( $query_notnull ); 
			$query_completed = $db->loadResult();

			echo "Processing jobs: ".$query_processing."<br />";
			echo "Completed jobs: ".$query_completed."<br /> <br />";

			/* set up database query to get user summary */
			echo "<h4> Users: </h4> <br/>";

			$query2 = "SELECT username, COUNT(CASE WHEN output_path IS NULL THEN job_id ELSE NULL END) AS processing, COUNT(CASE WHEN output_path IS NOT NULL THEN output_path ELSE NULL END) AS completed FROM r17ie_user_jobs GROUP BY username";

			$db->setQuery($query2);

			$rows = $db->loadRowList();

			/* No users in database */
			if ($rows <=0)
				echo "No users found <br />";

			/* One or more users */
			else {
				$index = 1;

				/* Repeatedly get information for each user */
				foreach ($rows as $row) {
					$username = $row[0];
					$no_processing = $row[1];
					$no_completed = $row[2];

					echo $username." has ".$no_processing." processing files and ".$no_completed." completed files.<br />";

					$index++;
				}
			}	

		}
	}

?>
