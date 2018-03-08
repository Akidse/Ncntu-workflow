<?php
if($profile->isLogged())
{
	$authorizeSession->clear();
	$router->redirect($router->url("auth"));
}
exit;