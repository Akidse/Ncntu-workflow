<?php

$sessionAlerts = new SessionAlerts();
switch($router->getAction())
{
	case 'requests':
	$requests = Database::query("SELECT * FROM `archive_requests`");
	if(!empty($router->getParams('confirm')) || !empty($router->getParams('reject')))
	{
		$requestId = intval(empty($router->getParams('confirm'))?$router->getParams('reject'):$router->getParams('confirm'));
		$request = Database::query("SELECT * FROM `archive_requests` WHERE `request_id` = ?", [$requestId], Database::SINGLE);
		if(!empty($request))
		{
			if(!empty($router->getParams('confirm')))
			{
				Database::query("UPDATE `departments_documents` SET `is_archived` = '1' WHERE `document_id` = ?", [$request['document_id']]);
				$sessionAlerts->add('Документ переміщений в архів', 'success');
			}
			else
			{
				$sessionAlerts->add('Переміщення в архів відхилено', 'success');
			}

			Database::query("DELETE FROM `archive_requests` WHERE `request_id` = ?", [$requestId]);
			$router->redirect('request/');
		}
	}
	Template::setTitle('Архів - заявки');
	break;
	default:
	$resultsPerPages = 10;

	$paginator = new Paginator(Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `is_archived` = 1"),
		$resultsPerPages, (!empty($router->getParams(0))?$router->getParams(0):null), '/panel/documents/page/(:num)');

	Database::setNextLimit($paginator->getDBLimit(), $resultsPerPages);
	$documents = Database::query("SELECT * FROM `departments_documents` WHERE `is_archived` = 1 ORDER BY `document_id` DESC");

	Template::setTitle('Архів документів');
	break;
}