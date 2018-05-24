<?php

Template::addCssFile('breadcrumps');

Template::setTitle(_("Installation"));
$sessionAlerts = new SessionAlerts();

if(Config::get('isInstalled'))$router->redirect($router->url());
switch($router->getQuery('step'))
{
	case '4':
	if(isset($_POST['schema']))
	{
		if($_POST['schema'] == 1)
		{
			$tables_schema = file_get_contents(PathManager::data('schemas/ncntu_schema.sql'));
			Database::query($tables_schema);
		}

		Config::set('isInstalled', 1);
		Config::save();
		$router->redirect($router->url('', 'panel'));
	}
	break;
	case '3':
	$postHandler = new PostRequestHandler([
		'fields' => [
			'email' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'password' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'password_confirm' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'first_name' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'middle_name' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'last_name' => [
				'required' => true,

				'type' => PostRequestHandler::STRING_TYPE,
			]
		],
		'validators' => [
			'email' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 4,
					'max' => 64
				],
			],
			'password' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 6,
					'max' => 21,
				],
			],
			'first_name' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 2,
					'max' => 64
				],
			],
			'middle_name' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 2,
					'max' => 64
				],
			],
			'last_name' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 2,
					'max' => 64
				],
			],
		],
	]);

	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('email'))$sessionAlerts->add(_("Email field is empty or not valid"), "error");
		if(!$postHandler->isValid('password'))$sessionAlerts->add(_("Password field is empty or not valid"), "error");
		if(!$postHandler->isValid('password_confirm'))$sessionAlerts->add(_("Password confirm field is empty"), "error");
		if(!$postHandler->isValid('first_name'))$sessionAlerts->add(_("First name is empty or it's length is not valid"), "error");
		if(!$postHandler->isValid('middle_name'))$sessionAlerts->add(_("Middle name is empty or it's length is not valid"), "error");
		if(!$postHandler->isValid('last_name'))$sessionAlerts->add(_("Last name is empty or it's length is not valid"), "error");

		if($postHandler->get('password') != $postHandler->get('password_confirm'))
		{
			$sessionAlerts->add(_("Passwords have to be the same"), "error");
			$postHandler->setValid(false);
		}

		if(!filter_var($postHandler->get('email'), FILTER_VALIDATE_EMAIL))
		{
			$sessionAlerts->add(_("Email is not valid"), "error");
			$postHandler->setValid(false);
		}

		if($postHandler->isValid())
		{
			$passwordHash = password_hash($postHandler->get('password'), PASSWORD_DEFAULT);
			$userId = Database::query("INSERT INTO `users` (`email`, `password_hash`, `first_name`, `middle_name`, `last_name`, `groups`) VALUES(?, ?, ?, ?, ?, ?)",
				[$postHandler->get('email'), $passwordHash, $postHandler->get('first_name'), $postHandler->get('middle_name'), $postHandler->get('last_name'), '1']);
			$sessionAlerts->add(_("Admin was created successfully"), "success");
			$authorizeSession->set($userId, $passwordHash, false);
			$router->redirect($router->url("?step=4"));
		}
	}
	break;
	case '2':
		$postHandler = new PostRequestHandler([
			'fields' => [
				'default_page_title' => [
					'required' => true,
				],
				'max_file_size' => [
					'required' => true,
					'type'=> PostRequestHandler::INT_TYPE
				],
				'default_locale' => [
					'required' => true
				],
				'recaptcha_private_key' => [],
				'recaptcha_public_key' => [],
			]
		]);

		if($postHandler->proceed())
		{
			if($postHandler->isValid())
			{
				Config::set('default_page_title', $postHandler->get('default_page_title'));
				Config::set('max_file_size', $postHandler->get('max_file_size'));
				Config::set('default_locale', $postHandler->get('default_locale'));
				Config::set('recaptcha_private_key', $postHandler->get('recaptcha_private_key'));
				Config::set('recaptcha_public_key', $postHandler->get('recaptcha_public_key'));
				Config::save();
				$sessionAlerts->add(_("Config saved successfully"), "success");
				$router->redirect($router->url("?step=3"));
			}
			else $sessionAlerts->add(_("Input data is incorrect"), "error");
		}
	break;
	case '1':
		$postHandler = new PostRequestHandler([
			'fields' => [
				'host' => [
					'required' => true,
				],
				'user' => [
					'required' => true,
				],
				'password' => [],
				'database_name' => [
					'required' => true,
				],
			]
		]);

		if($postHandler->proceed())
		{
			if($postHandler->isValid())
			{
				try
				{
					Database::connect($postHandler->get('host'), $postHandler->get('user'), $postHandler->get('password'), $postHandler->get('database_name'));
					Config::set('db_host', $postHandler->get('host'));
					Config::set('db_user', $postHandler->get('user'));
					Config::set('db_password', $postHandler->get('password'));
					Config::set('db_name', $postHandler->get('database_name'));
					Config::save();
					$tables_schema = file_get_contents(PathManager::data('schemas/tables_schema.sql'));
					Database::query($tables_schema);
					$sessionAlerts->add(_("Database connected"), "success");
					$router->redirect($router->url("/?step=2"));
				}
				catch (PDOException $e)
				{
					$sessionAlerts->add(_("Could not connect to database:").$e, "error");
				}
			}
			else $sessionAlerts->add(_("Input data is incorrect"), "error");
		}
	break;
}