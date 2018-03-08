<?php

class TextHelper
{
	public static function cut($text, $length, $withDots = false)
	{
		if($length == 0 || $length >= strlen($text))return $text;

		return mb_substr($text, 0, $length, 'utf-8').($withDots?'...': null);
	}
}