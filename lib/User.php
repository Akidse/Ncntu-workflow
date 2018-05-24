<?php

class User
{
	private $userData;
	private $department = null;
	private $groups = array();
	private $permissions = array();
	
	public function __construct($userId)
	{
		$userData = Database::query("SELECT * FROM `users` WHERE `user_id` = ?", [$userId], Database::SINGLE);
		if(!empty($userData))
		{
			$this->userData = $userData;

			$groups = explode(',', $this->get('groups'));
			$this->groups = Database::query("SELECT * FROM `users_groups` WHERE `group_id` IN(".str_repeat('?,', count($groups) - 1) . '?'.")", $groups);
			foreach($this->groups as $group)
			{
				$permitteds = Database::query("SELECT `permission_id` FROM `users_groups_permitted` WHERE `group_id` = ?", [$group['group_id']]);
				foreach($permitteds as $permitted)
				{
					$permission = Database::query("SELECT `name` FROM `users_groups_permissions` WHERE `permission_id` = ?", [$permitted['permission_id']], Database::SINGLE);
					$this->permissions[] = $permission['name'];
				}
			}
		}
		else
		{
			echo "Such user not found";
		}
	}

	public function get($key)
	{
		if(!isset($this->userData[$key]))return null;
		return $this->userData[$key];
	}

	public function getDepartment()
	{
		if(empty($this->get('department_id')))
			return 0;

		if($this->department == null)
			$this->department = new Department($this->get('department_id'));

		return $this->department;
	}

	public function getGroupsList()
	{
		$groupsList = "";
		foreach($this->groups as $group)$groupsList .= $group['name'].', ';
		return substr($groupsList,0,-2);
	}

	public function hasPermission($permission)
	{
		return in_array($permission, $this->permissions);
	}

	public function getFullName()
	{
		return $this->userData['last_name'].' '.$this->userData['first_name'].' '.$this->userData['middle_name'];
	}

	public function getAvatar()
	{
		if(empty($this->get('avatar')))return PathManager::image('noavatar.png', true);
		return PathManager::avatar($this->get('avatar'), true);
	}

	public function updateData()
	{
		if($this->profileType == self::TYPE_USER)$this->userData = Database::query("SELECT * FROM `users` WHERE `user_id` = ?", [$this->get('user_id')], Database::SINGLE);
	}
}