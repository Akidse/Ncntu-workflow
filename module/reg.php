<?php

if($authorizeSession->isAuthorized())
{
	$router->redirect("/");
}

Template::setTitle(_("Registration"));
Template::setBackButtonUrl($router->url("", "auth"));

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
			],
			'g-recaptcha-response' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
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
$sessionAlerts = new SessionAlerts();

if(isset($_POST['submit_form']))
{
	if($postHandler->proceed())
	{
		if(!$postHandler->isValid('email'))$sessionAlerts->add(_("Email field is empty or not valid"), "error");
		if(!$postHandler->isValid('password'))$sessionAlerts->add(_("Password field is empty or not valid"), "error");
		if(!$postHandler->isValid('password_confirm'))$sessionAlerts->add(_("Password confirm field is empty"), "error");
		if(!$postHandler->isValid('first_name'))$sessionAlerts->add(_("First name is empty or it's length is not valid"), "error");
		if(!$postHandler->isValid('middle_name'))$sessionAlerts->add(_("Middle name is empty or it's length is not valid"), "error");
		if(!$postHandler->isValid('last_name'))$sessionAlerts->add(_("Last name is empty or it's length is not valid"), "error");

		$recaptcha = new \ReCaptcha\ReCaptcha(Config::get('recaptcha_private_key'));
		$recaptchaResponse = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

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

		if(Database::query("SELECT COUNT(*) FROM `users` WHERE `email` = ?", [$postHandler->get('email')]) != 0)
		{
			$sessionAlerts->add(_("This email already registered"), "error");
			$postHandler->setValid(false);
		}

		if(!$recaptchaResponse->isSuccess())
		{
			$sessionAlerts->add(_("Captcha is not valid"), "error");
			$postHandler->setValid(false);
		}

		if($postHandler->isValid())
		{
			$passwordHash = password_hash($postHandler->get('password'), PASSWORD_DEFAULT);
			$userId = Database::query("INSERT INTO `users` (`email`, `password_hash`, `first_name`, `middle_name`, `last_name`) VALUES(?, ?, ?, ?, ?)",
				[$postHandler->get('email'), $passwordHash, $postHandler->get('first_name'), $postHandler->get('middle_name'), $postHandler->get('last_name')]);
			$sessionAlerts->add(_("You are registered successfully"), "success");
			$authorizeSession->set($userId, $passwordHash, false);
			$router->redirect($router->url("", "panel"));
		}
	}
	/*$readyForRegistration = true;
	$authData = array();

	if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))$readyForRegistration = false;
	$authData[] = $_POST['email'];

	if(strlen($_POST['password']) < 6)$readyForRegistration = false;
	if($_POST['password'] != $_POST['password_confirm'])$readyForRegistration = false;
	$authData[] = password_hash($_POST['password'], PASSWORD_DEFAULT);

	if(strlen($_POST['first_name']) <= 0 || strlen($_POST['first_name']) >= 64)$readyForRegistration = false;
	$authData[] = $_POST['first_name'];

	if(strlen($_POST['middle_name']) <= 0 || strlen($_POST['middle_name']) >= 64)$readyForRegistration = false;
	$authData[] = $_POST['middle_name'];

	if(strlen($_POST['last_name']) <= 0 || strlen($_POST['last_name']) >= 64)$readyForRegistration = false;
	$authData[] = $_POST['last_name'];

	$recaptcha = new \ReCaptcha\ReCaptcha(Config::get('recaptcha_private_key'));

	$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

	if(!$resp->isSuccess())$readyForRegistration = false;

	if($readyForRegistration == true)
	{
		$userId = Database::query("INSERT INTO `users` (`email`, `password_hash`, `first_name`, `middle_name`,`last_name`)VALUES(?,?,?,?,?)", $authData);
		$authorizeSession->set($userId, $authData[1], false);
		$router->redirect($router->url("", "panel"));
	}
	else
	{
		echo "Not ready";
	}
	*/
}
?>