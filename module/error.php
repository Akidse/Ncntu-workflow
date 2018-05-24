<?php

$errorInfo = [
	'403' => [
		'title' => _("403: Access forbidden"),
		'message' => _("403: Access forbidden"),
	],
	'404' => [
		'title' => _("404: Not found"),
		'message' => _("404: Not found"),
	],
];

$errorNum = $router->getQuery('type');
if(empty($router->getQuery('type')))
	$errorNum = '404';
Template::setTitle($errorInfo[$errorNum]['title']);