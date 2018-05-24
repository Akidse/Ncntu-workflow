<?php

function recursiveDepartmentArray($parentId = 0)
{
	$departments = Database::query("SELECT * FROM `departments` WHERE `subdepartment_id` = ?", [(int)$parentId]);
	$array = array();
	foreach($departments as $department)
	{
		$department['childs'] = recursiveDepartmentArray($department['department_id']);
		$array[] = $department;
	}
	return $array;
}

function recursiveDepartmentRemove($parentId)
{
	Database::query("DELETE FROM `departments` WHERE `department_id` = ?", [intval($parentId)]);
	Database::query("DELETE FROM `departments_permitted` WHERE `department_id` = ?", [intval($parentId)]);
	$departments = Database::query("SELECT * FROM `departments` WHERE `subdepartment_id` = ?", [(int)$parentId]);
	foreach($departments as $department)
	{
		recursiveDepartmentRemove($department['department_id']);
	}
}

$sessionAlerts = new SessionAlerts();

$permissions = Database::query("SELECT * FROM `departments_permissions`");
$permissionsFields = array();
foreach($permissions as $permission)
{
	$permissionsFields[$permission['name']] = [
									'required' => false,
									'type' => PostRequestHandler::BOOL_TYPE,
								];
}

$postHandler = new PostRequestHandler([
		'fields' => array_merge([
				'name' => [
					'required' => true,
					'type' => PostRequestHandler::STRING_TYPE
				],
				'public' => [
					'type' => PostRequestHandler::BOOL_TYPE,
					'default' => false,
					],
				'main' => [
					'type' => PostRequestHandler::BOOL_TYPE,
					'default' => false
				]
			], $permissionsFields),
		'validators' => [
				'name' => [
					PostRequestHandler::STRLEN_VALID => [
						'min' => 3,
						'max' => 255,
						],
					],
				],
		]);

Template::setTitle(_("Departments").' - '._("Admin panel"));
switch($router->getAction())
{
	case 'add':
	if($router->getParams(0) == null || ($router->getParams(0) != 0 && 
		Database::query("SELECT COUNT(*) FROM `departments` WHERE `department_id` = ?", [intval($router->getParams(0))]) == 0))
		$router->redirect($router->url());

	$departmentId = intval($router->getParams(0));

	if($postHandler->proceed())
	{
		if($postHandler->get('main') != false && Database::query("SELECT COUNT(*) FROM `departments` WHERE `main` = 1") != 0)
		{
			$sessionAlerts->add("Головна станція уже існує.", 'error');
			$postHandler->setValid(false);
		}
		if(!$postHandler->isValid('name'))
			$sessionAlerts->add("Занадно довга або занадто коротка назва станції", 'error');

		if($postHandler->isValid())
		{
			$newDepartmentId = Database::query("INSERT INTO `departments` (`name`, `subdepartment_id`, `public`, `main`)VALUES(?, ?, ?, ?)", 
				[$postHandler->get('name'), $router->getParams()[0], $postHandler->get('public'), $postHandler->get('main')]);
			$department = new Department($newDepartmentId);
			foreach($permissions as $permission)
			{
				$department->setPermission($permission['name'], $postHandler->get($permission['name']));
			}
			$sessionAlerts->add("Підрозділ створено успішно", 'success');
			$router->redirect($router->url());
		}
	}
	Template::setBackButtonUrl($router->url('', 'admin/departments'));
	break;
	case 'edit':
	if(empty($router->getParams(0)) || ($router->getParams(0) != 0 && 
		Database::query("SELECT COUNT(*) FROM `departments` WHERE `department_id` = ?", [intval($router->getParams(0))]) == 0))
		$router->redirect($router->url());

	$departmentId = intval($router->getParams(0));
	$department = new Department($departmentId);

	if($postHandler->proceed())
	{
		if($postHandler->get('main') != false && !$department->get('main') && Database::query("SELECT COUNT(*) FROM `departments` WHERE `main` = 1") != 0)
		{
			$sessionAlerts->add("Головна станція уже існує.", 'error');
			$postHandler->setValid(false);
		}
		if(!$postHandler->isValid('name'))
			$sessionAlerts->add("Занадно довга або занадто коротка назва станції", 'error');

		if($postHandler->isValid())
		{
			Database::query("UPDATE `departments` SET `name` = ?, `public` = ?, `main` = ? WHERE `department_id` = ?", [$postHandler->get('name'), $postHandler->get('public'), $postHandler->get('main'), $department->get('department_id')]);
			$department = new Department($departmentId);
			foreach($permissions as $permission)
			{
				$department->setPermission($permission['name'], $postHandler->get($permission['name']));
			}
			$sessionAlerts->add("Підрозділ відредаговано успішно", 'success');
		}
	}
	Template::setBackButtonUrl($router->url('', 'admin/departments'));
	break;
	case 'remove':
	if(!isset($router->getParams()[0]))$router->redirect($router->url());

	$departmentId = intval($router->getParams(0));
	$department = Database::query("SELECT * FROM `departments` WHERE `department_id` = ?", [$departmentId], DATABASE::SINGLE);
 	if($department == null)$router->redirect($router->url());

 	if(isset($_POST) && $_POST != null)
 	{
 		recursiveDepartmentRemove($department['department_id']);
 		$sessionAlerts->add("Підрозділ видалено", 'success');
 		$router->redirect($router->url());
 	}
	$departmentsToDelete = recursiveDepartmentArray($department['department_id']);
	Template::setBackButtonUrl($router->url('', 'admin/departments'));
	break;
	default:
    $departments = recursiveDepartmentArray(0);
    Template::setBackButtonUrl($router->url('', 'admin'));
	break;
}