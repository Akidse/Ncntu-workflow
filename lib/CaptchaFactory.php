<?php

class CaptchaFactory 
{
	public const RECAPTCHA = 0;
	public const STOCK_CAPTCHA = 1;
	private $captchaType = 0;
	private $captchaInstance;

	public function __construct($captchaType = 0)
	{
		$this->captchaType = $captchaType;
		if(Config::get('recaptcha_private_key') == null || Config::get('recaptcha_public_key') == null)
			$this->captchaType = self::STOCK_CAPTCHA;

		switch($this->captchaType)
		{
			case self::RECAPTCHA:
			$this->captchaInstance = new \ReCaptcha\ReCaptcha(Config::get('recaptcha_private_key'));
			break;
			case self::STOCK_CAPTCHA:
			$this->captchaInstance = new StockCaptcha();
			break;
		}
	}


	public function display()
	{
		switch($this->captchaType)
		{
			case self::RECAPTCHA:
			echo '<div class="g-recaptcha" data-sitekey="'.Config::get('recaptcha_public_key').'"></div>';
			break;
			case self::STOCK_CAPTCHA:
			return $this->captchaInstance->display();
			break;
		}
	}

	public function verify()
	{
		switch($this->captchaType)
		{
			case self::RECAPTCHA:
			if(!isset($_POST['g-recaptcha-response']))return false;
			$recaptchaResponse = $this->captchaInstance->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			return $recaptchaResponse->isSuccess();
			break;
			case self::STOCK_CAPTCHA:
			return $this->captchaInstance->verify();
			break;
		}		
	}
}