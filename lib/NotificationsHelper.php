<?php

class NotificationsHelper
{
	static function push($text, $userId)
	{
		Database::query("INSERT INTO `users_notifications` (`text`, `user_id`)VALUES(?, ?)", [$text, $userId]);
	}
}