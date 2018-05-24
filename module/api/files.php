<?php
function file_force_download($file, $name)
{
	if (file_exists($file))
	{
		if (ob_get_level())
		{
			ob_end_clean();
		}
		header('Content-Description: File Transfer');
		header('Content-Type: '.mime_content_type($file));
		header('Content-Disposition: attachment; filename=' . $name);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: no-cache');
		header('Pragma: public');
		header('Content-Length: ' . filesize($file));
		if ($fd = fopen($file, 'rb'))
		{
			while (!feof($fd))
			{
				print fread($fd, 1024);
			}
			fclose($fd);
		}
		exit;
	}
}
switch($router->getAction())
{
	case 'upload':
		$filesArray = FileHelper::diverseFileArray($_FILES['file']);
		$addedFilesIds = array();
		foreach($filesArray as $file)
		{
			$tempFile = $file['tmp_name'];
			$targetFileName = FileHelper::generateRandomName(pathinfo($file['name'], PATHINFO_EXTENSION));
			$targetFile = PathManager::file($targetFileName);
			move_uploaded_file($tempFile, $targetFile);
			$addedFilesIds[] = Database::query("INSERT INTO `files` (`name`, `real_name`, `user_id`)VALUES(?, ?, ?)", [$file['name'], $targetFileName, $profile->get("user_id")]);
		}
		echo json_encode($addedFilesIds);
	break;
	case 'get':
	$file = Database::query("SELECT * FROM `files` WHERE `file_id` = ?", [(int)$router->getParams(0)], Database::SINGLE);
	file_force_download(PathManager::file($file['real_name']), $file['name']);
	break;
	case 'previewer':
	if(empty($router->getParams(0)))exit;
	$file = new File(intval($router->getParams(0)));
	if($file->isExists())
	{
		$filePreviewer = new FilePreviewer($file);
		$filePreviewer->display();		
	}
	else
	{
		echo '<div class="alert alert-warning">'._("Such file does not exists").'</div>';
	}
	break;
	default:
	break;
}
exit;