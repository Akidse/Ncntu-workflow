<?php 

Template::setTitle('Профіль користувача');

if(!empty($router->getAction()) &&
	Database::query("SELECT COUNT(*) FROM `users` WHERE `user_id` = ?", [$router->getAction()]) != 0)
{
	$userProfile = new User($router->getAction());
}
else $userProfile = new User($profile->get('user_id'));

$userResume = Database::query("SELECT * FROM `users_resume` WHERE `user_id` = ?", [$userProfile->get('user_id')], Database::SINGLE);
if(empty($userResume))
{
	Database::query("INSERT INTO `users_resume` (`user_id`)VALUES(?)", [$userProfile->get('user_id')]);
	$userResume = Database::query("SELECT * FROM `users_resume` WHERE `user_id` = ?", [$userProfile->get('user_id')], Database::SINGLE);
}

$sessionAlerts = new SessionAlerts();
if(!empty($router->getAction()) && $router->getAction() == 'edit')
{
	$generalDataFormHandler = new PostRequestHandler([
		'fields' => [
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
			],
			'phone_number' => [
				'type' => PostRequestHandler::INT_TYPE,
			],
			'address' => [
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'city' => [
				'type' => PostRequestHandler::STRING_TYPE,
			],
		],
		'validators' => [
			'first_name' => [
				PostRequestHandler::STRLEN_VALID => [
				'min' => 2,
				'max' => 64,
				],
			],
			'middle_name' => [
				PostRequestHandler::STRLEN_VALID => [
				'min' => 2,
				'max' => 64,
				],
			],
			'last_name' => [
				PostRequestHandler::STRLEN_VALID => [
				'min' => 2,
				'max' => 64,
				],
			],
			'phone_number' => [
				PostRequestHandler::NUMBER_VALID => [],
			],
			'address' => [
				PostRequestHandler::STRLEN_VALID => [
				'max' => 128,
				],
			],
			'city' => [
				PostRequestHandler::STRLEN_VALID => [
				'max' => 64,
				],
			],
		],
	]);

	$avatarFormHandler = new PostRequestHandler([
		'fields' => [
			'avatar_file' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
		],
	]);

	$passwordFormHandler = new PostRequestHandler([
		'fields' => [
			'old_password' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'password' => [
				'required' => true,
				'type' =>  PostRequestHandler::STRING_TYPE,
			],
			'password_confirm' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
		],
		'validators' => [
			'old_password' => [
				PostRequestHandler::STRLEN_VALID => [
					'max' => 64,
					'min' => 6,
				],
			],
			'password' => [
				PostRequestHandler::STRLEN_VALID => [
					'max' => 64,
					'min' => 6,
				],
			],
			'password_confirm' => [
				PostRequestHandler::STRLEN_VALID => [
					'max' => 64,
					'min' => 6,
				],
			],
		],
	]);


	if($router->getParams(0) == 'general' && $generalDataFormHandler->proceed())
	{
		if(!$generalDataFormHandler->isValid('first_name'))$sessionAlerts->add('Ім\'я введено невірно', 'error');
		if(!$generalDataFormHandler->isValid('middle_name'))$sessionAlerts->add('По батькові введено невірно', 'error');
		if(!$generalDataFormHandler->isValid('last_name'))$sessionAlerts->add('Прізвище введено невірно', 'error');
		if(!$generalDataFormHandler->isValid('phone_number'))$sessionAlerts->add('Телефонний номер введений невірно', 'error');
		if(!$generalDataFormHandler->isValid('address'))$sessionAlerts->add('Адреса введена невірно', 'error');
		if(!$generalDataFormHandler->isValid('city'))$sessionAlerts->add('Місто введено невірно', 'error');

		if($generalDataFormHandler->isValid())
		{
			Database::query("UPDATE `users` SET `first_name` = ?, `middle_name` = ?, `last_name` = ? WHERE `user_id` = ?",
				[$generalDataFormHandler->get('first_name'), $generalDataFormHandler->get('middle_name'), $generalDataFormHandler->get('last_name'),
					$userProfile->get('user_id')]);
			Database::query("UPDATE `users_resume` SET `phone_number` = ?, `address` = ?, `city` = ? WHERE `user_id` = ?",
				[$generalDataFormHandler->get('phone_number'), $generalDataFormHandler->get('address'), $generalDataFormHandler->get('city'),
					$userProfile->get('user_id')]);
			$sessionAlerts->add('Профіль успішно відредагований', 'success');
			$logger->write('changed profile data', $profile);
			$profile->updateData();
			$userProfile = new User($profile->get('user_id'));
			$userResume = Database::query("SELECT * FROM `users_resume` WHERE `user_id` = ?", [$userProfile->get('user_id')], Database::SINGLE);
		}
	}


	if($router->getParams(0) == 'avatar' && $avatarFormHandler->proceed())
	{
		if(!$avatarFormHandler->isValid('avatar_file'))$sessionAlerts->add('При завантаженні файлу відбулися помилки', 'error');

		if($avatarFormHandler->isValid())
		{
			if($userProfile->get('avatar') != null)unlink(PathManager::avatar($userProfile->get('avatar')));
			Database::query("UPDATE `users` SET `avatar` = ? WHERE `user_id` = ?", [$avatarFormHandler->get('avatar_file'), $userProfile->get('user_id')]);
			$profile->updateData();
			$sessionAlerts->add('Аватар завантажений успішно', 'success');
			$logger->write('changed profile avatar', $profile);
		}
	}

	if($router->getParams(0) == 'password' && $passwordFormHandler->proceed())
	{
		if(!$passwordFormHandler->isValid('old_password'))$sessionAlerts->add('Старий пароль введено не вірно', 'error');
		if(!$passwordFormHandler->isValid('password'))$sessionAlerts->add('Новий пароль введено не вірно', 'error');
		if(!$passwordFormHandler->isValid('password_confirm'))$sessionAlerts->add('Підтвердження нового паролю введено не вірно', 'error');

		if(!password_verify($passwordFormHandler->get('old_password'), $profile->get('password_hash')))
		{
			$passwordFormHandler->setValid(false);
			$sessionAlerts->add('Старий пароль введено не вірно', 'error');
		}
		if($passwordFormHandler->get('password') != $passwordFormHandler->get('password_confirm'))
		{
			$passwordFormHandler->setValid(false);
			$sessionAlerts->add('Паролі не співпадають', 'error');
		}
		if($passwordFormHandler->isValid())
		{
			Database::query("UPDATE `users` SET `password_hash` = ? WHERE `user_id` = ?", 
				[password_hash($passwordFormHandler->get('password'), PASSWORD_DEFAULT), $userProfile->get('user_id')]);
			$profile->updateData();
			$authorizeSession->set($profile->get('user_id'), $profile->get('password_hash'));
			$sessionAlerts->add('Пароль успішно змінено', 'success');
			$logger->write('changed profile password', $profile);
		}
	}

	Template::addJsFile('module/dropzone');
	Template::setTitle('Редагування профілю');
	Template::setBackButtonUrl($router->url());
}