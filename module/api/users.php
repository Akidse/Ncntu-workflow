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
	case 'search':
	if(!empty($_POST))
	{
		if(isset($_POST['term']) && mb_strlen($_POST['term']) >= 3)
		{
			$result = Database::query("SELECT `user_id` FROM `users` WHERE `first_name` LIKE ? OR `last_name` LIKE ? OR `middle_name` LIKE ?",
				['%'.$_POST['term'].'%', '%'.$_POST['term'].'%', '%'.$_POST['term'].'%']);
			$jsonResult = array("results" => array());
			foreach($result as $user)
			{
				$tempUser = new User($user['user_id']);
				$jsonResult['results'][] = array('id' => $tempUser->get('user_id'), 'text' => $tempUser->getFullName());
			}

			echo json_encode($jsonResult);
		}
	}
	break;
	default:
	echo false;
	break;
}

die();