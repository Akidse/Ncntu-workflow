<?php

$sessionAlerts = new SessionAlerts();
$postHandler = new PostRequestHandler([
		'fields' => [
			'name' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'desc' => [
				'required' => false,
				'default' => null,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'mainFileId' => [
				'required' => true,
				'type' => PostRequestHandler::INT_TYPE 
			],
			'additionFilesId' => [
				'required' => false,
				'default' => null,
				'type' => PostRequestHandler::STRING_TYPE
			],
		],
		'validators' => [
			'name' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 3,
					'max' => 255,
				],
			],
			'desc' => [
				PostRequestHandler::STRLEN_VALID => [
					'max' => 1024,
				],
			],
		],
	]);

switch($router->getAction())
{
	case 'versions':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ? AND `department_id` = ?", 
		[intval($router->getParams(0)), $profile->get('department_id')])))$router->redirect($router->url());
	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$versions = Database::query("SELECT * FROM `departments_documents_versions` WHERE `document_id` = ? ORDER BY `version_id` DESC", [$document['document_id']]);

	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	Template::setTitle('Версії документу - '.$document['name']);	
	break;

	case 'delete':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ? AND `department_id` = ?", 
		[intval($router->getParams(0)), $profile->get('department_id')])))$router->redirect($router->url());
	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	if(!empty($router->getParams(1)))
	{
		FileHelper::removeFileById($document['file_id']);
		foreach(explode(',', $document['addition_id']) as $additionFileId)
		{
			FileHelper::removeFileById(intval($additionFileId));
		}
		$versions = Database::query("SELECT `file_id`, `addition_id` FROM `departments_documents_versions` WHERE `document_id` = ?", [$document['document_id']]);
		foreach($versions as $version)
		{
			FileHelper::removeFileById($version['file_id']);
			foreach(explode(',', $version['addition_id']) as $additionFileId)
			{
				FileHelper::removeFileById(intval($additionFileId));
			}
		}
		Database::query("DELETE FROM `departments_documents` WHERE `document_id` = ?", [$document['document_id']]);
		Database::query("DELETE FROM `departments_documents_versions` WHERE `document_id` = ?", [$document['document_id']]);
		Database::query("DELETE FROM `archive_requests` WHERE `document_id` = ?", [$document['document_id']]);
		$sessionAlerts->add("Документ видалений", 'success');
		$router->redirect($router->url());
	}
	break;
	case 'create':
	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add('Занадто довга або занадто коротка назва', 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add('Занадто довгий опис', 'error');
		if(!$postHandler->isValid('mainFileId'))$sessionAlerts->add('Виникли проблеми з завантаженням головного файлу', 'error');
		if(!$postHandler->isValid('additionFilesId'))$sessionAlerts->add('Виникли проблеми з завантаженням додаткових файлів', 'error');

		if($postHandler->isValid())
		{
			Database::query("INSERT INTO `departments_documents` (`name`, `description`, `user_id`, `department_id`, `file_id`, `addition_id`)VALUES(?, ?, ?, ?, ?, ?)",
				[$postHandler->get('name'), $postHandler->get('desc'), $profile->get('user_id'), $profile->get('department_id'), 
					$postHandler->get('mainFileId'), $postHandler->get('additionFilesId')]);
			$sessionAlerts->add("Документ успішно створений", 'success');
			$router->redirect($router->url());
		}
		else
		{
			FileHelper::removeFileById($postHandler->get('mainFileId'));
			foreach(explode(',', $postHandler->get('additionFilesId')) as $additionFileId)
			{
				FileHelper::removeFileById(intval($additionFileId));
			}
		}
	}

	$scriptManager->add("module/dropzone");
	Template::setBackButtonUrl($router->url('/'));
	Template::setTitle('Завантаження нового документу');
	break;

	case 'edit':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ? AND `department_id` = ?", 
		[intval($router->getParams(0)), $profile->get('department_id')])))$router->redirect($router->url());
	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$mainFile = new File($document['file_id']);
	$additionFiles = array();
	foreach(explode(',', $document['addition_id']) as $additionFileId)
	{
		$additionFiles[] = new File($additionFileId);
	}

	$postHandler->addInputs([
			'fields' => [
				'files_to_remove' => [
					'required' => false,
					'default' => null,
					'type' => PostRequestHandler::STRING_TYPE,	
				],
				'mainFileId' => [
					'required' => false,
					'default' => null,
					'type' => PostRequestHandler::INT_TYPE,
				],
			],
		]);

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add('Занадто довга або занадто коротка назва', 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add('Занадто довгий опис', 'error');
		if(!$postHandler->isValid('mainFileId'))$sessionAlerts->add('Виникли проблеми з завантаженням головного файлу', 'error');
		if(!$postHandler->isValid('additionFilesId'))$sessionAlerts->add('Виникли проблеми з завантаженням додаткових файлів', 'error');

		$isAnyChanges = (!empty($postHandler->get('additionFilesId')) || !empty($postHandler->get('mainFileId')) || !empty($postHandler->get('files_to_remove')) ||
			$document['name'] != $postHandler->get('name') || $document['description'] != $postHandler->get('desc'));
		if($postHandler->isValid() && $isAnyChanges)
		{
			$mainFileId = $document['file_id'];
			$additionFiles = $document['addition_id'];
			if(!empty($postHandler->get('files_to_remove')))
			{
				$additionFiles = explode(',', $additionFiles);
				$filesToRemove = explode(',', $postHandler->get('files_to_remove'));
				$additionFiles = implode(',', array_diff($additionFiles, $filesToRemove));
			}
			Database::query("INSERT INTO `departments_documents_versions` (`document_id`, `name`, `description`, `user_id`, `department_id`, `file_id`, 
				`addition_id`, `time_created`, `time_deprecated`, `editor_id`)VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
				[$document['document_id'], $document['name'], $document['description'], $document['user_id'], $document['department_id'], 
				$document['file_id'], $document['addition_id'], $document['time_updated'], TimeHelper::getCurrentTimestamp(),
				$profile->get('user_id')]);

			if(!empty($postHandler->get('mainFileId')))$mainFileId = $postHandler->get('mainFileId');
			if(!empty($postHandler->get('additionFilesId')))$additionFiles = $additionFiles.','.$postHandler->get('additionFilesId');

			Database::query("UPDATE `departments_documents` SET `name` = ?, `description` = ?, `file_id` = ?, `addition_id` = ?, `time_updated` = ? WHERE `document_id` = ?",
				[$postHandler->get('name'), $postHandler->get('desc'), $mainFileId, $additionFiles, TimeHelper::getCurrentTimestamp(), $document['document_id']]);

			$sessionAlerts->add("Документ успішно відредагований", 'success');
			$router->redirect($router->url('view/'.$document['document_id']));
		}
		else
		{
			FileHelper::removeFileById($postHandler->get('mainFileId'));
			foreach(explode(',', $postHandler->get('additionFilesId')) as $additionFileId)
			{
				FileHelper::removeFileById(intval($additionFileId));
			}
		}
	}

	$scriptManager->add("module/dropzone");
	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	Template::setTitle('Редагування документу - '.$document['name']);
	break;

	case 'send-to-archive':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ? AND `department_id` = ?", 
		[intval($router->getParams(0)), $profile->get('department_id')])))$router->redirect($router->url());
	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	if(Database::query("SELECT COUNT(*) FROM `archive_requests` WHERE `document_id` = ? AND `type` = '0'", [$document['document_id']]) != 0)
		$router->redirect($router->url('/view/'.$document['document_id']));
	
	if(!empty($router->getParams(1)))
	{
		Database::query("INSERT INTO `archive_requests` (`document_id`, `user_id`, `time`, `type`)VALUES(?, ?, ?, ?)",
			[$document['document_id'], $profile->get('user_id'), TimeHelper::getCurrentTimestamp(), 0]);
		$sessionAlerts->add('Заявка на додання в архів зроблена', 'success');
		$router->redirect($router->url('/view/'.$document['document_id']));
	}

	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	break;

	case 'view':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());

	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);

	if(!$profile->hasPermission('handle_archive') && $document['department_id'] != $profile->get('department_id') && !$document['is_archived'])
		$router->redirect($router->url());

	$versionMode = false;
	if(!empty($router->getParams('version')))
	{
		$version = Database::query("SELECT * FROM `departments_documents_versions` WHERE `document_id` = ? AND `version_id` = ?", 
			[$document['document_id'], intval($router->getParams('version'))], Database::SINGLE);
		if(!empty($version))
		{
			$versionMode = true;
			$document = $version;
			$editor = new User($document['editor_id']);
		}
	}
	$versionsCount = Database::query("SELECT COUNT(*) FROM `departments_documents_versions` WHERE `document_id` = ?", [$document['document_id']]);
	$author = new User($document['user_id']);
	$mainFile = new File($document['file_id']);
	$additionFiles = array();
	foreach(explode(',', $document['addition_id']) as $additionFileId)
	{
		$additionFiles[] = new File($additionFileId);
	}
	$filePreviewer = new FilePreviewer(new File($document['file_id']));

	$isSentToArchive = Database::query("SELECT COUNT(*) FROM `archive_requests` WHERE `document_id` = ? AND `type` = '0'", [$document['document_id']]);

	Template::setTitle('Перегляд документу - '.$document['name']);
	Template::setBackButtonUrl($router->url('/')); 
	if($versionMode)Template::setBackButtonUrl($router->url('/versions/'.$document['document_id'])); 
	break;

	default:
	$resultsPerPages = 10;

	$paginator = new Paginator(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0",[intval($profile->get('department_id'))]),
		$resultsPerPages, (!empty($router->getParams(0))?$router->getParams(0):null), '/panel/documents/page/(:num)');

	Database::setNextLimit($paginator->getDBLimit(), $resultsPerPages);
	$documents = Database::query("SELECT * FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 ORDER BY `document_id` DESC", [intval($profile->get('department_id'))]);

	Template::setTitle('Документи станції');
	break;
}


