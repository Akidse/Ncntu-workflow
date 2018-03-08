<?php

if($authorizeSession->isAuthorized())
{
	$router->redirect("/");
}

$title = "Registration";

if(isset($_POST['submit_form']))
{
	$readyForRegistration = true;
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

	$recaptcha = new \ReCaptcha\ReCaptcha('6Lel9jIUAAAAACd41vxlcPGz9NXtAsWLOcVzQHSF');

	$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

	if (!$resp->isSuccess())$readyForRegistration = false;

	if($readyForRegistration == true)
	{
		Database::query("INSERT INTO `users` (`email`, `password_hash`, `first_name`, `middle_name`,`last_name`)VALUES(?,?,?,?,?)", $authData);
		echo "Registered";
	}
	else
	{
		echo "Not ready";
	}
}
?>