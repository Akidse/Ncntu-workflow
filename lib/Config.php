<?php


class Config
{
	private static $configs;

	public static function load($configFilePath)
	{
		self::$configs = parse_ini_file($configFilePath, true);
	}

	public static function get($var)
	{
		return self::$configs[$var];
	}
}