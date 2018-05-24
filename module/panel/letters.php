<?php
$sessionAlerts = new SessionAlerts();
switch($router->getAction())
{
	case 'view':
	if(empty($router->getParams(0)) || Database::query("SELECT COUNT(*) FROM `letters` WHERE (`user_id` = ? OR `receiver_id` = ?) AND `letter_id` = ?",
			[$profile->get('user_id'), $profile->get('user_id'), intval($router->getParams(0))]) == 0)
			$router->redirect();
	$letter = Database::query("SELECT * FROM `letters` WHERE `letter_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$author = new User($letter['user_id']);
	$receiver = new User($letter['receiver_id']);

	if($receiver->get('user_id') == $profile->get('user_id') && $letter['unread'])
		Database::query("UPDATE `letters` SET `unread` = 0 WHERE `letter_id` = ?", [$letter['letter_id']]);

	
	$files = array();
	if(!empty($letter['files']))
	{
		$filesId = explode(',', $letter['files']);
		foreach($filesId as $fileId)
		{
			$files[] = new File($fileId);
		}
	}
	$filesListTemplate = new TemplateModule('module/FilesList');

	if($author->get('user_id') == $profile->get('user_id'))
		Template::setBackButtonUrl($router->url('outbox/'));
	else 
		Template::setBackButtonUrl($router->url('inbox/'));
	break;
	case 'create':
	$jsonReceiver = null;
	if(!empty($router->getParams(0)) && $router->getParams(0) != $profile->get('user_id') &&
		Database::query("SELECT COUNT(*) FROM `users` WHERE `user_id` = ?", [$router->getParams(0)]) != 0)
	{
		$receiver = new User($router->getParams(0));
		$jsonReceiver = json_encode(['id' => $receiver->get('user_id'), 'text' => $receiver->getFullName()]);
	}
	$postHandler = new PostRequestHandler([
			'fields' => [
				'receiver' => [
					'type' => PostRequestHandler::INT_TYPE,
					'required' => true,
				],
				'subject' => [
					'type' => PostRequestHandler::STRING_TYPE,
					'required' => false,
					'default' => null,
				],
				'message' => [
					'type' => PostRequestHandler::STRING_TYPE,
					'required' => false,
					'default' => null,
				],
				'filesid' => [
					'type' => PostRequestHandler::STRING_TYPE,
					'required' => false,
					'default' => null,
				],
			],
			'validators' => [
				'subject' => [
					PostRequestHandler::STRLEN_VALID => [
						'max' => 512,
					],
				],
				'message' => [
					PostRequestHandler::STRLEN_VALID => [
						'max' => 2048,
					],
				],
			],
		]);

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('receiver'))$sessionAlerts->add('Отримувач має бути визначений', 'error');
		if(!$postHandler->isValid('subject'))$sessionAlerts->add('Тема не має бути більше 512 символів', 'error');
		if(!$postHandler->isValid('message'))$sessionAlerts->add('Повідомлення не має бути більше 2048 символів', 'error');

		if(empty($postHandler->get('subject')) && empty($postHandler->get('message')) && empty($postHandler->get('filesid')))
		{
			$postHandler->setValid(false);
			$sessionAlerts->add('Лист не повинен бути пустим', 'error');
		}

		if($postHandler->isValid())
		{
			Database::query("INSERT INTO `letters` (`user_id`, `receiver_id`, `subject`, `message`, `files`, `time_created`) VALUES(?, ?, ?, ?, ?, ?)",
				[$profile->get('user_id'), $postHandler->get('receiver'), $postHandler->get('subject'), $postHandler->get('message'), $postHandler->get('filesid'),
					TimeHelper::getCurrentTimestamp()]);
			$sessionAlerts->add('Лист надісланий успішно', 'success');
			$router->redirect($router->url('outbox'));
		}
		else
		{
			foreach(explode(',', $postHandler->get('filesid')) as $fileId)
			{
				FileHelper::removeFileById(intval($fileId));
			}
		}
	}
	Template::addCssFile('select2.min');
	Template::addJsFile('module/select2.min');
	Template::addJsFile('module/dropzone');
	Template::setTitle("Створення листа");
	break;
	case 'view':

	break;
	case 'inbox':
	$paginator = new Paginator(
		Database::query("SELECT COUNT(*) FROM `letters` WHERE `receiver_id` = ?",[$profile->get('user_id')]),
		$profile->get('results_per_page'),
		$router->getQuery('page'),
		$router->url('inbox/?page=(:num)'));

	Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));
	$letters = Database::query("SELECT * FROM `letters` WHERE `receiver_id` = ? ORDER BY `unread` DESC, `letter_id` DESC", [$profile->get('user_id')]);

	Template::setTitle('Вхідні листи');
	break;
	case 'outbox':
	$paginator = new Paginator(
		Database::query("SELECT COUNT(*) FROM `letters` WHERE `user_id` = ?",[$profile->get('user_id')]),
		$profile->get('results_per_page'),
		$router->getQuery('page'),
		$router->url('outbox/?page=(:num)'));

	Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));
	$letters = Database::query("SELECT * FROM `letters` WHERE `user_id` = ? ORDER BY `letter_id` DESC", [$profile->get('user_id')]);

	Template::setTitle('Вихідні листи');
	break;
	default:

	$router->redirect($router->url("/inbox"));
	break;
}