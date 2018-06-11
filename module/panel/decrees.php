<?php

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
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'additionFilesId' => [
				'required' => false,
				'default' => null,
				'type' => PostRequestHandler::STRING_TYPE,
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
					'max' => 4096,
				],
			],
		],
	]);
$sessionAlerts = new SessionAlerts();
switch($router->getAction())
{
	case 'remove-confirm':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `decrees` WHERE `decree_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());
		$decree = Database::query("SELECT * FROM `decrees` WHERE `decree_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
		FileHelper::removeFileById($decree['file_id']);
		foreach(explode(',', $decree['additions_id']) as $additionFileId)
		{
			FileHelper::removeFileById(intval($additionFileId));
		}
		Database::query("DELETE FROM `decrees` WHERE `decree_id` = ?", [intval($router->getParams(0))]);
		$sessionAlerts->add(_("Decree removed"), 'success');
		$logger->write('removed decree[id='.$decree['decree_id'].']', $profile);
		$router->redirect($router->url('', 'panel/decrees'));
	break;
	case 'remove':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `decrees` WHERE `decree_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());
		$decree = Database::query("SELECT * FROM `decrees` WHERE `decree_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	Template::setTitle(_('Remove a decree'));
	Template::setBackButtonUrl($router->url('view/'.$decree['decree_id']));
	break;
	case 'view':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `decrees` WHERE `decree_id` = ?", 
		[intval($router->getParams(0))])))$router->redirect($router->url());

	$decree = Database::query("SELECT * FROM `decrees` WHERE `decree_id` = ?", [intval($router->getParams(0))], Database::SINGLE);

	$author = new User($decree['author_id']);
	$mainFile = new File($decree['file_id']);
	$additionFiles = array();
	foreach(explode(',', $decree['additions_id']) as $additionFileId)
	{
		$additionFiles[] = new File($additionFileId);
	}
	$filePreviewer = new FilePreviewer(new File($decree['file_id']));

	Template::setTitle(_("Decree view").' - '.$decree['name']);
	Template::setBackButtonUrl($router->url('/'));
	break;
	case 'create':
	if(!$profile->hasPermission('handle_decrees'))$router->redirect($router->url());

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add(_('Too long or too small name'), 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add(_('Too long description'), 'error');
		if(!$postHandler->isValid('mainFileId'))$sessionAlerts->add(_('Problems with uploading main file'), 'error');
		if(!$postHandler->isValid('additionFilesId'))$sessionAlerts->add(_('Problems with uploading addition files'), 'error');

		if($postHandler->isValid())
		{
			$decreeId = Database::query("INSERT INTO `decrees` (`name`, `description`, `author_id`, `public`, `file_id`, `additions_id`)VALUES(?, ?, ?, ?, ?, ?)",
				[$postHandler->get('name'), $postHandler->get('desc'), $profile->get('user_id'), true,
					$postHandler->get('mainFileId'), $postHandler->get('additionFilesId')]);
			$sessionAlerts->add(_('Decree published successfully'), 'success');
			$logger->write('create decree[id='.$decreeId.']', $profile);
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

	Template::addJsFile('module/dropzone');
	Template::setTitle("Create a decree");
	break;
	default:
	$decrees = Database::query("SELECT * FROM `decrees` WHERE `public` = '1'");
	Database::query('UPDATE `users` SET `decree_last_view` = ? WHERE `user_id` = ?', [TimeHelper::getCurrentTimestamp(), $profile->get('user_id')]);
	Template::setTitle(_("Decrees"));
	break;
}