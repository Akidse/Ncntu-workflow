<?php
$notifications = Database::query("SELECT * FROM `users_notifications` WHERE `unread` = 1 AND `user_id` = ?", [$profile->get('user_id')]);
Database::query("UPDATE `users_notifications` SET `unread` = 0 WHERE `user_id` = ?", [$profile->get('user_id')]);