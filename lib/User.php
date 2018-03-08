<?php

class User
{
	private $data;
	public function __construct($userId)
	{
		$this->data = Database::query("SELECT * FROM `users` WHERE `user_id` = ?", [$userId], Database::SINGLE);
	}

	public function get($key)
	{
		return $this->data[$key];
	}

	public function getFullName()
	{
		return $this->data['last_name'].' '.$this->data['first_name'].' '.$this->data['middle_name'];
	}
}