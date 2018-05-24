<?php
if($authorizeSession->isAuthorized())
{
	$router->redirect("/");
}
$postHandler = new PostRequestHandler([
		'fields' => [
			'login' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'password' => [
				'required' => true,
				'type' => PostRequestHandler::STRING_TYPE,
			],
			'save-cookie' => [
				'required' => false,
				'default' => false,
				'type' => PostRequestHandler::BOOL_TYPE,
			],
		],
		'validators' => [
			'login' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 1,
					'max' => 64,
				],
			],
			'password' => [
				PostRequestHandler::STRLEN_VALID => [
					'min' => 1,
					'max' => 64,
				],
			],
		],
	]);
$sessionAlerts = new SessionAlerts();
if($postHandler->proceed())
{
	if(!$postHandler->isValid('login'))$sessionAlerts->add("E-mail can't be less then 1 and more then 64", "error");
	if(!$postHandler->isValid('password'))$sessionAlerts->add("Incorrect password", "error");
	if(!filter_var($postHandler->get('login'), FILTER_VALIDATE_EMAIL))
	{
		$sessionAlerts->add("Incorrect E-mail", "error");
		$postHandler->setValid(false);
	}
	$userData = Database::query("SELECT `user_id`, `password_hash` FROM `users` WHERE `email` = ?",
		[$postHandler->get('login')], Database::SINGLE);
	if(empty($userData) || !password_verify($postHandler->get('password'), $userData['password_hash']))
	{
		$sessionAlerts->add("Incorrect password or E-mail", "error");
		$postHandler->setValid(false);
	}

	if($postHandler->isValid())
	{
		$authorizeSession->set($userData['user_id'], $userData['password_hash'], $postHandler->get('save-cookie'));
		$router->redirect($router->url("/", "panel"));
	}

	$router->redirect('');
}
else
{
	$router->redirect("/");
}
?>