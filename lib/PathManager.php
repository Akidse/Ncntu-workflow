<?php
class PathManager
{
	public static function getRootDirectory()
	{
		return $_SERVER['DOCUMENT_ROOT'];
	}
	public static function template($routeName, $subfolder = null)
	{
		$subfolder = ($subfolder == null ? '':$subfolder.'/');
		return self::getRootDirectory().'/template/'.$subfolder.$routeName.'.phtml';
	}

	public static function script($scriptName, $shortUrl = true)
	{
		if($shortUrl)return '/public/scripts/'.$scriptName.'.js';
		return self::getRootDirectory().'/public/scripts/'.$scriptName.'.js';
	}
	public static function module($module)
	{
		$moduleTypeName = ($module->getTypeName() == null ? '' : $module->getTypeName().'/');
		if(file_exists(self::getRootDirectory().'/module/'.$moduleTypeName.$module->getName().'.php'))
			return self::getRootDirectory().'/module/'.$moduleTypeName.$module->getName().'.php';
		return false;
	}
	public static function file($fileName = null, $shortUrl = false)
	{
		if($shortUrl)return '/files/'.$fileName;
		return $_SERVER['DOCUMENT_ROOT'].'/files/'.$fileName;
	}
	public static function page($routeName)
	{

		return self::getRootDirectory().'/module/'.$routeName.'.php';
	}

	public static function site($path)
	{
		$path = ltrim($path, '/');
		if(isset($_SERVER['https']))
			return "https://".$_SERVER['SERVER_NAME']."/".$path;
		else 
			return "http://".$_SERVER['SERVER_NAME']."/".$path;
	}
}