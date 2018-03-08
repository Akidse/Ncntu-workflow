<?php

class Profile
{
	public const TYPE_GUEST = 0;
	public const TYPE_USER = 1;

	private $profileType = self::TYPE_GUEST;
	private $userData = array();
	private $department = null;
	private $groups = array();
	private $permissions = array();

	public function setUser(int $userId)
	{
		$userData = Database::query("SELECT * FROM `users` WHERE `user_id` = ?", [$userId], Database::SINGLE);
		if(!empty($userData))
		{
			$this->userData = $userData;
			$this->profileType = self::TYPE_USER;

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
	}

	public function get($var)
	{
		return $this->userData[$var];
	}
	public function getProfileType()
	{
		return $this->profileType;
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

	public function isLogged()
	{
		return $this->profileType == self::TYPE_USER;
	}
}