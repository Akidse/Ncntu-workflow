<?php

$sessionAlerts = new SessionAlerts();
Template::setTitle(_("Users management")." - "._("Admin panel"));
switch($router->getAction())
{
	case 'edit':
	$postHandler = new PostRequestHandler([
			'fields' => [
				'groups' => [
					'required' => true,
				],
				'department_id' => [
					'required' => true,
				],
			],
		]);
	if(Database::query("SELECT COUNT(*) FROM `users` WHERE `user_id` = ?", [$router->getParams(0)]) == 0)
		$router->redirect($router->url());
	$user = new User($router->getParams(0));
	if($postHandler->proceed())
	{
		if($postHandler->isValid()){
			Database::query("UPDATE `users` SET `groups` = ?, `department_id` = ? WHERE `user_id` = ?", [$postHandler->get('groups'), $postHandler->get('department_id'), $user->get('user_id')]);
			$user = new User($router->getParams(0));
			$sessionAlerts->add(_("Changes saved successfully"), "success");
		}
	}
	$userGroups = array_filter(explode(',', $user->get('groups')));
	$placeholders = implode(',', array_fill(0, count($userGroups), '?'));
	if(count($userGroups))$groups = Database::query("SELECT * FROM `users_groups` WHERE `group_id` NOT IN (".$placeholders.")", $userGroups);
	else $groups = Database::query("SELECT * FROM `users_groups`");
	$groupsJson = [];
	foreach($userGroups as $groupId){
		$group = Database::query("SELECT * FROM `users_groups` WHERE `group_id` = ?", [$groupId], Database::SINGLE);
		$groupsJson[] = [$group['group_id'], $group['name']];
	}
	$groupsJson = json_encode($groupsJson);
	$groupsArrayJson = json_encode($userGroups);
	Template::setBackButtonUrl($router->url('list', 'admin/users/list'));
	Template::addJsFile("module/departmentSelect");
	break;
	case 'remove':
	if(Database::query("SELECT COUNT(*) FROM `users` WHERE `user_id` = ?", [$router->getParams(0)]) == 0)
		$router->redirect($router->url());

	if($router->getQuery("confirm"))
	{
		Database::query("DELETE FROM `users` WHERE `user_id` = ?", [intval($router->getParams(0))]);
		$sessionAlerts->add(_("User removed successfully"), "success");
		$router->redirect($router->url('list', 'admin/users'));
	}

	Template::setBackButtonUrl($router->url('list', 'admin/users'));
	Template::setTitle(_("Remove a user")." - "._("Admin panel"));
	break;
	case 'list':
	$pagination = new Paginator(
		Database::query("SELECT COUNT(*) FROM `users`"),
		$profile->get('results_per_page'),
		$router->getQuery('page'),
		$router->url("list/?page=(:num)"));
	Database::setNextLimit($pagination->getDBLimit(), $profile->get("results_per_page"));
	$users = Database::query("SELECT `user_id`, `first_name`, `middle_name`, `last_name` FROM `users`");
	Template::setBackButtonUrl($router->url('', 'admin/users'));
	Template::setTitle(_("List of users")." - "._("Admin panel"));
	break;
	default:
	Template::setBackButtonUrl($router->url('', 'admin'));
	break;
}