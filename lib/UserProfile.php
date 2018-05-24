<?php

class UserProfile extends User implements iProfile
{
	public function __construct($userId)
	{
		parent::__construct($userId);
	}
	public function isLogged()
	{
		return true;
	}
}