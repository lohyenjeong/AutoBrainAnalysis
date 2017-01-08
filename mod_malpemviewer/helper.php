<?php
	class ModMalpemViewerHelper {
		public static function getFiles() {
			/* set up database query to get files for that user */
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$currentusername = $user->username;	
			$deleted = 0;
			$query = $db->getQuery(true)
						->select($db->quoteName(array('time_submitted','time_ended','expected_output','age','gender', 'output_path')))
						->from($db->quoteName('r17ie_user_jobs'))
						->where($db->quoteName('username')."=".$db->quote($currentusername))
						->where($db->quoteName('deleted')."=".$db->quote($deleted))
						->order('time_submitted ASC');

			$db->setQuery($query);
			$rows = $db->loadRowList();

			/* User hasn't uploaded any files */
			if ((count($rows))<=0) {
				echo "<br /><br />Hello, ".$user->name.". You haven't uploaded any files yet.<br /><br />";
			}
			
			/* One or more files */
			else {
				echo "<br /><br />Hello, ".$user->name.". Here are your files:<br /><br />";
				$index = 1;

				/* Repeatedly get information for each file */
				foreach ($rows as $row) {
					$time_submitted = $row[0];
					$time_ended = $row[1];
					$expected_output = $row[2];
					$age = $row[3];
					$gender = $row[4];
					$output_path = $row[5];

					echo "Time submitted: ".$time_submitted."<br />";
					echo "Report name (when completed): ".$expected_output."<br />";

					echo "Status: ";
					if ($time_ended=="0000-00-00 00:00:00")
						echo "Processing";
					else if (!(is_null($output_path))) {
						echo "Complete: <a href= /joomla/imperialoutput/".$expected_output."> Click Here to Download </a>";
					}

					echo "<br />";

					if (!(is_null($age)))
						echo "Age: ".$age."<br />";
				
					if (!(is_null($gender)))
						echo "Gender: ".$gender."<br />";
					echo "<br />";

					$index++;
				}
			}	
		}

		public static function showFile($filenumber) {
			/* set up database query to get files for that user */
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$currentusername = $user->username;	

			$query2 = $db->getQuery(true)
						->select($db->quoteName(array('job_id','expected_output','time_submitted','output_path')))
						->from($db->quoteName('r17ie_user_jobs'))
						->where($db->quoteName('username')."=".$db->quote($currentusername))
						->order('time_submitted ASC');

			$db->setQuery($query2);
			$rows = $db->loadRowList();

			$filenumber--;
			// prevent out of bounds index access
			if ($filenumber>=0 && $filenumber<=$rows) {
				$output_path = $rows[$filenumber][3]."/";

				// test code - delete
				echo "File name / output: ".$output_path."\n";

				echo "Current dir: ".getcwd()."\n";

				$exploded = explode("/",$output_path);
				$filename = $exploded[6];
				$folder = $exploded[5];

				// get filename
				// get folder name

				$download_link = "/".$folder."/".$filename."/";

				echo "Download link: ".$download_link."\n";

				header('Content-Transfer-Encoding: binary');
				header('Content-type: application/zip');
				header('Content-disposition: attachment'); 

				readfile($output_path);
				
			}
		}

		public static function submit() {
			/* set up database query to delete files */
			$zero_value = '0';
			$deleted_files = "";
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$currentusername = $user->username;	
			$query2 = $db->getQuery(true)
						->select($db->quoteName(array('output_path','expected_output','deleted')))
						->from($db->quoteName('r17ie_user_jobs'))
						->where($db->quoteName('username').'='.$db->quote($currentusername));
			$db->setQuery($query2);
			$rows = $db->loadRowList();

			$fields = array($db->quoteName('deleted').'=1');

			if ((count($rows))<=0) {
				echo nl2br("No files available for deletion. \n");
 
			}
			
			else {
				foreach ($rows as $row) {
					$output_path = $row[0];
					$expected_output = $row[1];
					$deleted = $row[2];

					if ($deleted == '0') {
						//not deleted yet, so delete this report file
						$del_result = unlink($output_path);
						//for testing, comment the above and use $del_result=1 or 0 instead
						if ($del_result) {
							$deleted_files = $expected_output." \n".$deleted_files;
							$conditions = array($db->quoteName('output_path').'='.$db->quote($output_path));
							$updateQuery = $db->getQuery(true)
													->update($db->quoteName('r17ie_user_jobs'))
													->set($fields)
													->where($conditions);
							$db->setQuery($updateQuery);
							$result = $db->query();
						} 
						else {
							echo nl2br("Failed to delete file: ".$expected_output.", please try again. \n");
						}
					}
				}
			echo nl2br("Files deleted: "."\n".$deleted_files."\n");
			}
		}
	}
?>
