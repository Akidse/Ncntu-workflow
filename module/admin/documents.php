<?php

Template::setTitle('Управління документами');
Template::setBackButtonUrl($router->url('/', 'admin'));
$paginator = new Paginator(
	Database::query("SELECT COUNT(*) FROM `departments_documents`"),
	$profile->get('results_per_page'),
	$router->getQuery("page"),
	$router->url("/?page=(:num)"));
Database::setNextLimit($paginator->getDBLimit(), $profile->get('results_per_page'));
$documents = Database::query("SELECT * FROM `departments_documents` ORDER BY `document_id` DESC");	