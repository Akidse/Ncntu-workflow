<?php

$sessionAlerts = new SessionAlerts();

$postHandler = new PostRequestHandler([
	'fields' => [
		'default_page_title' => [
			'required' => true,
		],
		'default_locale' => [
			'required' => true,
		],
		'recaptcha_public_key' => [
			'required' => true,
		],
		'recaptcha_private_key' => [
			'required' => true,
		],
		'max_file_size' => [
			'required' => true,
		],
	],
]);

if($postHandler->proceed() && $postHandler->isValid())
{	
	Config::set('default_page_title', $postHandler->get('default_page_title'));
	Config::set('default_locale', $postHandler->get('default_locale'));
	Config::set('recaptcha_public_key', $postHandler->get('recaptcha_public_key'));
	Config::set('recaptcha_private_key', $postHandler->get('recaptcha_private_key'));
	Config::set('max_file_size', $postHandler->get('max_file_size'));
	Config::save();
	$sessionAlerts->add(_("Changes saved successfully"), "success");
}

Template::setTitle(_("System settings"));
Template::setBackButtonUrl($router->url("", "admin"));