<?php

function parentsList($child, $array = array())
{
	$parent_id = Database::query("SELECT `subdepartment_id` FROM `departments` WHERE `department_id` = ?", [(int)$child], Database::FETCH_COLUMN);

	if($parent_id == null)return $array;

	$array[] = $parent_id;
	return parentsList($parent_id, $array);
}
switch($router->getAction())
{
	case 'getlist':
	if(isset($_POST['subdepartment_id']))
	{
		$departments = Database::query("SELECT * FROM `departments` WHERE `subdepartment_id` = ?", [(int)$_POST['subdepartment_id']]);
		echo json_encode($departments);
	}
	else echo false;
	break;

	case 'getparentslist':
	if(isset($_POST['department_id']))
	{
		if(Database::query("SELECT * FROM `departments` WHERE `department_id` = ?", [(int)$_POST['department_id']]) != null)
		echo json_encode(parentsList($_POST['department_id'], [$_POST['department_id']]));
		else echo json_encode([0]);
	}
	else echo json_encode([0]);
	break;
	default:
	echo false;
	break;
}

die();