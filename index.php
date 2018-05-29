<?php

error_reporting(-1);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

define('ROOT', dirname(__FILE__));

session_start();
include_once ROOT."/lib/autoload.php";

if(!file_exists(PathManager::data('config.ini')))
	copy(PathManager::data('config_default.ini'), PathManager::data('config.ini'));

Config::load(ROOT."/data/config.ini");

$profile = new GuestProfile();
include_once ROOT."/lib/locale.php";

if(!empty(Config::get('db_host')) && !empty(Config::get('db_user')) && !empty(Config::get('db_name')))
Database::connect(Config::get("db_host"), Config::get("db_user"), Config::get("db_password"), Config::get("db_name"));

$router = new Router();

$title = 'Title';
$templateType = 'plain';

$scriptManager = new ScriptManager();

$authorizeSession = new AuthorizeSession();

if(!Config::get('isInstalled'))
{
	if($router->getModule()->getName() != 'install')$router->redirect($router->url('', 'install'));
	include_once PathManager::module(new Module('install'));
	include_once PathManager::template("common");
	die();
}

if($authorizeSession->try())
{
	$profile = new UserProfile($authorizeSession->getUserId());
}

if(!$router->isAvailableForGuests() && !$profile->isLogged())
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
	$router->redirect($router->url('', '404'));
	exit;
}
include_once PathManager::template("common");
?>