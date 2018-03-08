<?php

switch($router->getAction())
{
	case 'getbydepartment':
	if(isset($_POST['department_id']))
	{
		$users = Database::query("SELECT * from `users` WHERE `department_id` = ?", [(int)$_POST['department_id']]);
		echo json_encode($users);
	}
	else echo false;
	break;

	default:
	echo false;
	break;
}

die();