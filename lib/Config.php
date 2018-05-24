<?php


class Config
{
	private static $configs;
	private static $configFilePath;

	public static function load($configFilePath)
	{
		self::$configFilePath = $configFilePath;
		self::$configs = parse_ini_file($configFilePath, false);
	}

	public static function get($var)
	{
		if(!isset(self::$configs[$var]))return null;
		return self::$configs[$var];
	}

	public static function set($key, $value)
	{
		self::$configs[$key] = $value;
	}

	public static function save($file = null)
	{
		$config = self::$configs;
		$file = self::$configFilePath;
		$has_section = false;
		$write_to_file = true;

		$fileContent = '';
		if(!empty($config))
		{
			foreach($config as $i => $v)
			{
				if(is_array($v))
				{
					foreach($v as $t => $m)
					{
						$fileContent .= $i."[".$t."] = ".(is_numeric($m) ? $m : '"'.$m.'"') . "\n\r";
					}
				}
				else $fileContent .= $i . " = " . (is_numeric($v) ? $v : '"'.$v.'"') . "\n\r";
			}
		}

		file_put_contents($file, $fileContent, LOCK_EX);
	}
}