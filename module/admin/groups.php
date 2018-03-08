<?php

$postHandler = new PostRequestHandler([
				'fields' => [
					'name' => [
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
				],
		]);
$sessionAlerts = new SessionAlerts();
switch($router->getAction())
{
	case 'permissions':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `users_groups` WHERE `group_id` = ?", [intval($router->getParams(0))])))
		$router->redirect($router->url());
	$group = Database::query("SELECT * FROM `users_groups` WHERE `group_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
	$permissionsList = Database::query("SELECT * FROM `users_groups_permissions`");

	if(!empty($_POST))
	{
		foreach($permissionsList as $permission)
		{
			if(!empty($_POST[$permission['name']]))
			{
				if(Database::query("SELECT COUNT(*) FROM `users_groups_permitted` WHERE `permission_id` = ? AND `group_id` = ?",
					[$permission['permission_id'], $group['group_id']]) == 0)
					Database::query("INSERT INTO `users_groups_permitted` (`permission_id`, `group_id`)VALUES(?, ?)",
						[$permission['permission_id'], $group['group_id']]);
			}
			else
			{
				if(Database::query("SELECT COUNT(*) FROM `users_groups_permitted` WHERE `permission_id` = ? AND `group_id` = ?",
					[$permission['permission_id'], $group['group_id']]) != 0)
					Database::query("DELETE FROM `users_groups_permitted` WHERE  `permission_id` = ? AND `group_id` = ?",
						[$permission['permission_id'], $group['group_id']]);
			}
		}
		$sessionAlerts->add('Права успішно відредаговані', 'success');
	}
	Template::setBackButtonUrl($router->url('/'));
	break;
	case 'add':
	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add("Занадто коротке або задовге ім'я", 'error');

		if($postHandler->isValid())
		{
			Database::query("INSERT INTO `users_groups` (`name`)VALUES(?)", [$postHandler->get('name')]);
			$sessionAlerts->add("Група користувачів успішно добавлена", 'success');
			$router->redirect($router->url());
		}
	}
	break;
	case 'edit':
	if(empty($router->getParams(0)) || empty(Database::query("SELECT COUNT(*) FROM `users_groups` WHERE `group_id` = ?", [intval($router->getParams(0))])))
		$router->redirect($router->url());
	$group = Database::query("SELECT * FROM `users_groups` WHERE `group_id` = ?", [intval($router->getParams(0))], Database::SINGLE);

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('name'))$sessionAlerts->add("Занадто коротке або задовге ім'я", 'error');

		if($postHandler->isValid())
		{
			Database::query("UPDATE `users_groups` SET `name` = ? WHERE `group_id` = ?", 
				[$postHandler->get('name'), $group['group_id']]);
			$group = Database::query("SELECT * FROM `users_groups` WHERE `group_id` = ?", [intval($router->getParams(0))], Database::SINGLE);
			$sessionAlerts->add("Група користувачів успішно відредагована", 'success');
		}
	}
	break;
	case 'add':
	default:
	$groups = Database::query("SELECT * FROM `users_groups`");
	break;
}