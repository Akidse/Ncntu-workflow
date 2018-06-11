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

	public static function script($scriptName, $isUrl = true)
	{
		if($isUrl)return '/public/scripts/'.$scriptName.'.js';
		return self::getRootDirectory().'/public/scripts/'.$scriptName.'.js';
	}

	public static function image($imageName, $isUrl = true)
	{
		if($isUrl)return '/public/img/'.$imageName;
		return self::getRootDirectory().'/public/img/'.$imageName;
	}

	public static function style($styleName, $isUrl = true)
	{
		if($isUrl)return '/public/css/'.$styleName.'.css';
		return self::getRootDirectory().'/public/css/'.$styleName.'.css';		
	}

	public static function font($fontName, $isUrl = true)
	{
		if($isUrl)return '/public/webfonts/'.$fontName;
		return self::getRootDirectory().'/public/webfonts/'.$fontName;
	}

	public static function data($dataFile)
	{
		return self::getRootDirectory().'/data/'.$dataFile;
	}

	public static function module($module)
	{
		$moduleTypeName = ($module->getTypeName() == null ? '' : $module->getTypeName().'/');
		if(file_exists(self::getRootDirectory().'/module/'.$moduleTypeName.$module->getName().'.php'))
			return self::getRootDirectory().'/module/'.$moduleTypeName.$module->getName().'.php';
		return false;
	}

	public static function file($fileName = null, $isUrl = false)
	{
		if($isUrl)return '/files/'.$fileName;
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

	public static function templateModule($path)
	{
		if(file_exists(self::getRootDirectory().'/template/'.$path.'.phtml'))
			return self::getRootDirectory().'/template/'.$path.'.phtml';

		return null;
	}

	public static function avatar($fileName, $isUrl = false)
	{
		$fileName = ltrim($fileName, '/');

		return self::file('avatars/'.$fileName, $isUrl);
	}

	public static function logs($logFileName)
	{
		return self::getRootDirectory().'/data/logs/'.$logFileName.'.log';
	}
}