<?php


class AuthorizeSession
{
	private $userId = 0;
	public function try()
	{
		if(isset($_COOKIE['user_id']) && isset($_COOKIE['password_hash']))
		{
			$this->userId = $this->validateLoginData($_COOKIE['user_id'], $_COOKIE['password_hash']);
		}
		else if(isset($_SESSION['user_id']) && isset($_SESSION['password_hash']))
		{
			$this->userId = $this->validateLoginData($_SESSION['user_id'], $_SESSION['password_hash']);
		}

		return $this->userId;
	}

	public function validateLoginData(int $userId, string $password_hash)
	{
		$userData = Database::query("SELECT `user_id` FROM `users` WHERE `user_id` = ? AND `password_hash` = ?", 
			[intval($userId), $password_hash], Database::SINGLE);

		if(!empty($userData))return $userData['user_id'];
		else return 0;
	}

	public function set(int $userId, string $password_hash, $longSession = false)
	{
		if($longSession)
		{
			setcookie("user_id", $userId, time()+3600*24*7, '/');
			setcookie("password_hash", $password_hash, time()+3600*24*7, '/');
		}
		else
		{
			$_SESSION['user_id'] = $userId;
			$_SESSION['password_hash'] = $password_hash;
		}
	}

	public function clear()
	{
		setcookie("user_id", "", time() - 3600);
		setcookie("password_hash", "", time() - 3600);
		$_SESSION['user_id'] = null;
		$_SESSION['password_hash'] = null;
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function isAuthorized()
	{
		return ($this->userId != 0);
	}
}