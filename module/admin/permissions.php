<?php

$postHandler = new PostRequestHandler([
				'fields' => [
					'name' => [
						'required' => true,
					],
					'desc' => [
						'required' => true,
					],
				],
				'validators' => [
					'name' => [
						PostRequestHandler::STRLEN_VALID => [
							'min' => 1,
							'max' => 64,
						],
					],					
					'desc' => [
						PostRequestHandler::STRLEN_VALID => [
							'min' => 1,
							'max' => 255,
						],
					],
				],
		]);
$sessionAlerts = new SessionAlerts();
switch($router->getAction())
{
	case 'delete':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `users_groups_permissions` WHERE `permission_id` = ?", [intval($router->getParams(0))])))
		$router->redirect($router->url());
	$permission = Database::query("SELECT * FROM `users_groups_permissions` WHERE `permission_id` = ?", [intval($router->getParams(0))], Database::SINGLE);

	Database::query("DELETE FROM `users_groups_permitted` WHERE `permission_id` = ?", [$permission['permission_id']]);
	Database::query("DELETE FROM `users_groups_permissions` WHERE `permission_id` = ?", [$permission['permission_id']]);
	$sessionAlerts->add("Успішно видалено", 'error');
	$router->redirect($router->url());
	break;
	case 'add':
	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add("Занадто коротке або задовге ім'я", 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add("Занадто короткий або задовгий опис", 'error');

		if($postHandler->isValid())
		{
			Database::query("INSERT INTO `users_groups_permissions` (`name`, `description`)VALUES(?, ?)", [$postHandler->get('name'), $postHandler->get('desc')]);
			$sessionAlerts->add("Привілегія успішно добавлена", 'success');
			$router->redirect($router->url());
		}
	}
	break;
	case 'edit':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `users_groups_permissions` WHERE `permission_id` = ?", [intval($router->getParams(0))])))
		$router->redirect($router->url());
	$permission = Database::query("SELECT * FROM `users_groups_permissions` WHERE `permission_id` = ?", [intval($router->getParams(0))], Database::SINGLE);

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add("Занадто коротке або задовге ім'я", 'error');
		if(!$postHandler->isValid('desc'))$sessionAlerts->add("Занадто короткий або задовгий опис", 'error');

		if($postHandler->isValid())
		{
			Database::query("UPDATE `users_groups_permissions` SET `name` = ?, `description` = ? WHERE `permission_id` = ?", 
				[$postHandler->get('name'),$postHandler->get('desc'), $permission['permission_id']]);
			$permission = Database::query("SELECT * FROM `users_groups_permissions` WHERE `permission_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
			$sessionAlerts->add("Привілегія успішно відредагована", 'success');
		}
	}
	break;
	case 'add':
	default:
	$permissions = Database::query("SELECT * FROM `users_groups_permissions`");
	break;
}