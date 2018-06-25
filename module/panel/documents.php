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
	case 'request':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());

	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$isPrivate = $document['is_private'];
	if($isPrivate && $document['user_id'] != $profile->get('user_id'))$router->redirect($router->url());
	if(isset($_POST['department']) && Database::query("SELECT COUNT(*) FROM `departments` WHERE `department_id` = ?", [intval($_POST['department'])]) != 0)
	{
		Database::query("INSERT INTO `documents_requests` (`type`, `department_id`, `document_id`, `user_id`, `description`) VALUES(?, ?, ?, ?, ?)",
			['sign', $_POST['department'], $document['document_id'], $profile->get('user_id'), $_POST['description']]);
		$sessionAlerts->add(_("Signature request was sent, you will be notice"), 'success');
		$router->redirect($router->url("view/".$document['document_id'], "panel/documents"));
	}
	$departments = Database::query("SELECT `department_id`, `name` FROM `departments`");
	Template::setBackButtonUrl($router->url("view/".$document['document_id'], "panel/documents"));
	Template::setTitle(_("Create a signature request"));
	break;
	case 'versions':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ? AND `department_id` = ?", 
		[intval($router->getParams(0)), $profile->get('department_id')])))$router->redirect($router->url());
	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$versions = Database::query("SELECT * FROM `departments_documents_versions` WHERE `document_id` = ? ORDER BY `version_id` DESC", [$document['document_id']]);

	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	Template::setTitle(_("Document's versions").' - '.$document['name']);	
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
		$sessionAlerts->add(_("Document was removed"), 'success');
		$logger->write('removed document[id='.$document['document_id'].']', $profile);
		$router->redirect($router->url());
	}
	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	Template::setTitle(_("Remove a document").' - '.$document['name']);
	break;
	case 'create':
	$isPrivate = ($router->getQuery('private') == 1);//nice bug
	$sqlIsPrivate = ($isPrivate == true?'1':'0');//"nice solution", hehe
	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add(_('Too long or too small name'), 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add(_('Too long description'), 'error');
		if(!$postHandler->isValid('mainFileId'))$sessionAlerts->add(_('Problems with uploading main file'), 'error');
		if(!$postHandler->isValid('additionFilesId'))$sessionAlerts->add(_('Problems with uploading addition files'), 'error');

		if($postHandler->isValid())
		{
			$documentId = Database::query("INSERT INTO `departments_documents` (`name`, `description`, `user_id`, `department_id`, `file_id`, `addition_id`, `is_private`)VALUES(?, ?, ?, ?, ?, ?, ?)",
				[$postHandler->get('name'), $postHandler->get('desc'), $profile->get('user_id'), $profile->get('department_id'), 
					$postHandler->get('mainFileId'), $postHandler->get('additionFilesId'), $sqlIsPrivate]);
			$sessionAlerts->add(_("Document was created successfully"), 'success');
			$logger->write('create document[id='.$documentId.']', $profile);
			$router->redirect($router->url(($isPrivate?'?private':null)));
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
	Template::setTitle(_("New department document"));
	if($isPrivate)
	{
		Template::setTitle(_("New private document"));
	}
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
		if(!$postHandler->isValid('name'))$sessionAlerts->add(_('Too long or too small name'), 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add(_('Too long description'), 'error');
		if(!$postHandler->isValid('mainFileId'))$sessionAlerts->add(_('Problems with uploading main file'), 'error');
		if(!$postHandler->isValid('additionFilesId'))$sessionAlerts->add(_('Problems with uploading addition files'), 'error');

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

			$sessionAlerts->add(_("Changes to document are saved"), 'success');
			$logger->write('edited document[id='.$document['document_id'].']', $profile);
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
	Template::setTitle(_("Edit the document").' - '.$document['name']);
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
		$sessionAlerts->add(_("Archive request was created"), 'success');
		$logger->write('sent document[id='.$document['document_id'].'] to archive', $profile);
		$router->redirect($router->url('/view/'.$document['document_id']));
	}

	Template::setBackButtonUrl($router->url('/view/'.$document['document_id']));
	Template::setTitle(_("Create request to archive").' - '.$document['name']);
	break;

	case 'view':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `document_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());

	$document = Database::query("SELECT * FROM `departments_documents` WHERE `document_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$isPrivate = $document['is_private'];
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
	$filePreviewer = new FilePreviewer($mainFile);

	$isSentToArchive = Database::query("SELECT COUNT(*) FROM `archive_requests` WHERE `document_id` = ? AND `type` = '0'", [$document['document_id']]);

	Template::setTitle(_("Document view").' - '.$document['name']);
	Template::setBackButtonUrl($router->url(($isPrivate?'?private':null))); 
	if($versionMode)Template::setBackButtonUrl($router->url('/versions/'.$document['document_id'])); 

	$toolbar = new Toolbar();
	if(!$versionMode && !$document['is_archived'])
	{
		if(!$isSentToArchive)
		{
			if(!$isPrivate)$toolbar->addButton($router->url('send-to-archive/'.$document['document_id']), _("Send to archive"));
			if($isPrivate)$toolbar->addButton($router->url('move/'.$document['document_id']), _("Move to department"));
			$toolbar->addButton($router->url('edit/'.$document['document_id']), _("Edit"));
			if(($isPrivate && $document['user_id'] == $profile->get('user_id')) || !$isPrivate)
				$toolbar->addButton($router->url('request/'.$document['document_id'].'/sign'), _("Create signature request"));
		}
		$toolbar->addButton($router->url('delete/'.$document['document_id']), _('Remove'), 'btn-danger');
	}
	break;

	case 'search':
	$isPrivate = !empty($router->getQuery('private'));
	if(isset($_POST['search']) && strlen($_POST['search']) < 128 && strlen($_POST['search']) >= 3)
	{
		echo Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private`= ? AND (`name` LIKE ? OR `description` LIKE ?)", 
		[intval($profile->get('department_id')), $isPrivate, '%'.$_POST['search'].'%', '%'.$_POST['search'].'%']);
		$paginator = new Paginator(
		Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private`= ? AND (`name` LIKE ? OR `description` LIKE ?)", 
		[intval($profile->get('department_id')), $isPrivate, '%'.$_POST['search'].'%', '%'.$_POST['search'].'%']),
		$profile->get('results_per_page'),
		$router->getQuery("page"),
		$router->url("/?page=(:num)"));

		Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));

		$documents = Database::query("SELECT * FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private`= ? AND (`name` LIKE %?% OR `description` LIKE %?%)",
			[intval($profile->get('department_id')), $isPrivate, '%'.$_POST['search'].'%', '%'.$_POST['search'].'%']);
	}
	else
	{
		$documents = null;
		$sessionAlerts->add(_("Search keywords must be 3 or more/less then 128 long"), 'warning');
	}
	break;

	default:
	$isPrivate = !empty($router->getQuery('private'));
	$isSearch = !empty($router->getQuery('search'));
	if($isSearch && strlen($router->getQuery('search')) < 128 && strlen($router->getQuery('search')) >= 3)
	{
		$keywords = '%'.urldecode($router->getQuery('search')).'%';
		$paginator = new Paginator(
		Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private`= ? AND (`name` LIKE ? OR `description` LIKE ?)", 
		[intval($profile->get('department_id')), $isPrivate, $keywords, $keywords]),
		$profile->get('results_per_page'),
		$router->getQuery("page"),
		$router->url("/?private=".$router->getQuery('private')."&search=".$router->getQuery('search')."&page=(:num)"));

		Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));

		$documents = Database::query("SELECT * FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private`= ? AND (`name` LIKE ? OR `description` LIKE ?)",
			[intval($profile->get('department_id')), $isPrivate, $keywords, $keywords]);
		Template::setBackButtonUrl($router->url('/', 'panel/documents'));
	}
	else
	{
		$paginator = new Paginator(
			Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private` = ?", 
			[intval($profile->get('department_id')), $isPrivate]),
			$profile->get('results_per_page'),
			$router->getQuery("page"),
			$router->url("/?page=(:num)"));

		Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));
		$documents = Database::query("SELECT * FROM `departments_documents` WHERE `department_id` = ? AND `is_archived` = 0 AND `is_private` = ? ORDER BY `document_id` DESC",
			[intval($profile->get('department_id')), $isPrivate]);		
	}
	Template::setTitle(_("Department documents"));
	if($isPrivate)Template::setTitle(_("My documents"));
	break;
}


