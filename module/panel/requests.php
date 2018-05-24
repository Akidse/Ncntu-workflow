<?php
$sessionAlerts = new SessionAlerts();
if($router->getAction() == 'done' && !empty($router->getParams(0)))
{
	Database::query("UPDATE `documents_requests`  SET `date_done` = ? WHERE `request_id` = ?", [TimeHelper::getCurrentTimestamp(), $router->getParams(0)]);
	$sessionAlerts->add('Запит виконаний, відправник сповіщений', 'success');
	$router->redirect($router->url('inbox', 'panel/requests'));
}
if($router->getAction() == 'inbox')
{
	$isInbox = true;
	$requests = Database::query("SELECT * FROM `documents_requests` WHERE `department_id` = ? AND `date_done` IS NULL", [$profile->get('department_id')]);
	Template::setTitle('Вхідні запити');
}
else
{
	$isInbox = false;
	$requests = Database::query("SELECT * FROM `documents_requests` WHERE `user_id` = ? AND `date_done` IS NULL", [$profile->get('user_id')]);
	Template::setTitle('Вихідні запити');
}
