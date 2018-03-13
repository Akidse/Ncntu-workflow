<?php
class Template
{
	private static $data = array();
	private static $cssFiles = array();
	private static $jsFiles = array();

	public static function setTitle(string $title = null)
	{
		if($title != null)
			self::$data['title'] = $title;
	}

	public static function getTitle()
	{
		if(!empty(self::$data['title']))return self::$data['title'];
		else return Config::get('default_page_title');
	}

	public static function setBackButtonUrl($url = '/')
	{
		self::$data['back_button_url'] = $url;
	}

	public static function getBackButtonUrl()
	{
		if(!empty(self::$data['back_button_url']))return self::$data['back_button_url'];
		else return false;
	}

	public static function addCssFile($name)
	{
		self::$cssFiles[] = $name;
	}

	public static function addJsFile($name)
	{
		self::$jsFiles[] = $name;
	}

	public static function getCssFiles()
	{
		return self::$cssFiles;
	}

	public static function getJsFiles()
	{
		return self::$jsFiles;
	}
}