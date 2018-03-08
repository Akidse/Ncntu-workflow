<?php

error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

define('ROOT', dirname(__FILE__));

session_start();
include_once ROOT."/lib/autoload.php";

Config::load(ROOT."/data/config.ini");

Database::connect(Config::get("db_host"), Config::get("db_user"), Config::get("db_password"), Config::get("db_name"));

$router = new Router();

$profile = new Profile();
$title = 'Title';
$templateType = 'plain';

$scriptManager = new ScriptManager();

$authorizeSession = new AuthorizeSession();
if($authorizeSession->try())
{
	$profile->setUser($authorizeSession->getUserId());
}
else if(!$router->isAvailableForGuests() && $profile->getProfileType() == Profile::TYPE_GUEST)
{
	$router->redirect('');
}

include_once PathManager::module(new Module("module.settings", $router->getModule()->getType()));

if(PathManager::module($router->getModule()))
{
	include_once PathManager::module($router->getModule());
}
else
{
	echo "Not Found".$router->getModule()->getTypeName();

}
include_once PathManager::template("common");
?>