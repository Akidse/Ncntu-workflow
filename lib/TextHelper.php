<?php

class TextHelper
{
	public static function cut($text, $length, $withDots = false)
	{
		if($length == 0 || $length >= strlen($text))return $text;

		return mb_substr($text, 0, $length, 'utf-8').($withDots?'...': null);
	}

	public static function decline($num, $form_for_1, $form_for_2, $form_for_5){
		$num = abs($num) % 100;
		$num_x = $num % 10;
		if ($num > 10 && $num < 20)
			return $form_for_5;
		if ($num_x > 1 && $num_x < 5)
			return $form_for_2;
		if ($num_x == 1)
			return $form_for_1;
		return $form_for_5;
	}
}