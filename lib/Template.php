<?php
class Template
{
	private static $data = array();

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
}