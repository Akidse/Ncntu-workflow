<?php
$scriptManager->add("module/departmentSelect");

Template::setTitle(_("Users departments")." - "._("Admin panel"));
Template::setBackButtonUrl($router->url("", "admin/users"));
if(isset($_POST) && $_POST != null)
{
	foreach($_POST as $key => $value)
	{
		if(preg_match("/department_[0-9]/i", $key))
		{
			$userId = preg_replace("/department_/", "", $key);
			Database::query("UPDATE `users` SET `department_id` = ? WHERE `user_id` = ?", [$value, $userId]);
		}
	}
}

$departments = Database::query("SELECT * FROM `departments`");
$users = Database::query("SELECT `user_id`, `first_name`, `middle_name`, `last_name`, `department_id` FROM `users`");