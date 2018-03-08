<?php

class TimeHelper
{
	public static function getCurrentTimestamp()
	{
		return date('Y-m-d H:i:s', time());
	}
}
?>