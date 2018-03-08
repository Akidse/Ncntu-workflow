<?php
$scriptManager->add("module/dropzone");

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
				'type' => PostRequestHandler::INT_TYPE],
			'additionFilesId' => [
				'required' => false,
				'default' => null,
				'type' => PostRequestHandler::STRING_TYPE],
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
		$router->redirect($router->url('', 'panel/documents'));
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