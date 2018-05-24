<?php
function imageResize($target, $newcopy, $w, $h, $ext)
{
    list($w_orig, $h_orig) = getimagesize($target);
    $scale_ratio = $w_orig / $h_orig;
    if (($w / $h) > $scale_ratio) {
           $w = $h * $scale_ratio;
    } else {
           $h = $w / $scale_ratio;
    }
    $img = "";
    $ext = strtolower($ext);
    if ($ext == "gif"){ 
      $img = imagecreatefromgif($target);
    } else if($ext =="png"){ 
      $img = imagecreatefrompng($target);
    } else { 
      $img = imagecreatefromjpeg($target);
    }
    $tci = imagecreatetruecolor($w, $h);
    imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
    imagejpeg($tci, $newcopy, 80);
}
switch($router->getAction())
{
	case 'getbydepartment':
	if(isset($_POST['department_id']))
	{
		$users = Database::query("SELECT * from `users` WHERE `department_id` = ?", [(int)$_POST['department_id']]);
		echo json_encode($users);
	}
	else echo false;
	break;
	case 'search':
	if(!empty($_POST))
	{
		if(isset($_POST['term']) && mb_strlen($_POST['term']) >= 3)
		{
			$result = Database::query("SELECT `user_id` FROM `users` WHERE `first_name` LIKE ? OR `last_name` LIKE ? OR `middle_name` LIKE ?",
				['%'.$_POST['term'].'%', '%'.$_POST['term'].'%', '%'.$_POST['term'].'%']);
			$jsonResult = array("results" => array());
			foreach($result as $user)
			{
				$tempUser = new User($user['user_id']);
				$jsonResult['results'][] = array('id' => $tempUser->get('user_id'), 'text' => $tempUser->getFullName());
			}

			echo json_encode($jsonResult);
		}
	}
	break;
	case 'upload_avatar':
		$filesArray = FileHelper::diverseFileArray($_FILES['file']);
		$file = $filesArray[0];
		$tempFile = $file['tmp_name'];
		$targetFileName = FileHelper::generateRandomName(pathinfo($file['name'], PATHINFO_EXTENSION));
		$targetFile = PathManager::avatar($targetFileName);
		imageResize($tempFile, $tempFile, 300, 300, pathinfo($file['name'], PATHINFO_EXTENSION));
		move_uploaded_file($tempFile, $targetFile);
		echo json_encode($targetFileName);
	break;
	default:
	echo false;
	break;
}

die();