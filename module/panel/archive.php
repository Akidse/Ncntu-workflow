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
				$sessionAlerts->add(_("Document added to archive"), 'success');
			}
			else
			{
				$sessionAlerts->add(_("Request to archive was declined"), 'success');
			}

			Database::query("DELETE FROM `archive_requests` WHERE `request_id` = ?", [$requestId]);
			$router->redirect($router->url('', 'panel/archive'));
		}
	}
	Template::setTitle(_("Archive requests"));
	break;
	default:
	$paginator = new Paginator(
		Database::query("SELECT COUNT(*) FROM `departments_documents` WHERE `is_archived` = 1"),
		$profile->get('results_per_page'),
		$router->getQuery('page'),
		$router->url('?page=(:num)', 'panel/archive'));

	Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));
	$documents = Database::query("SELECT * FROM `departments_documents` WHERE `is_archived` = 1 ORDER BY `document_id` DESC");

	Template::setTitle(_("Archive"));
	break;
}