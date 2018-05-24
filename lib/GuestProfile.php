<?php

class GuestProfile implements iProfile
{
	private $guestData = array();

	public function __construct()
	{
		$this->guestData['ip'] = $_SERVER['REMOTE_ADDR'];
		$this->guestData['locale'] = Config::get('default_locale');
	}

	public function get($var)
	{
		if(isset($this->guestData[$var]))return $this->guestData[$var];
		else return "null";
	}
	public function isLogged()
	{
		return false;
	}
}