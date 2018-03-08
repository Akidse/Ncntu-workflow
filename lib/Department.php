<?php


class Department
{
	private $data;
	private $permissions;
	public function __construct($id)
	{
		$this->data = Database::query("SELECT * FROM `departments` WHERE `department_id` = ?", [intval($id)], Database::SINGLE);
		$this->permissions = Database::query("SELECT * FROM `departments_permitted` WHERE `department_id` = ?", [intval($id)]);
	}

	public function get($key)
	{
		return $this->data[$key];
	}

	public function getPermission($permissionName)
	{
		$permission = Database::query("SELECT * FROM `departments_permissions` WHERE `name` = ?", [strval($permissionName)], Database::SINGLE);
		if(empty($permission))return false;

		foreach($this->permissions as $permitted)
		{
			if($permitted['permission_id'] == $permission['permission_id'])return true;
		}

		return false;
	}

	public function getPermissionsList()
	{
		$permissionsList = array();
		
		foreach($this->permissions as $permitted)
		{
			$permissionsList[] = Database::query("SELECT * FROM `departments_permissions` WHERE `permission_id` = ?", 
				[intval($permitted['permission_id'])], Database::SINGLE);
		}

		return $permissionsList;
	}

	public function getChildrens()
	{
		$childrensData = Database::query("SELECT `department_id` FROM `departments` WHERE `subdepartment_id` = ?", [intval($this->get('department_id'))]);
		$childrens = array();
		foreach($childrensData as $child)
		{
			$childrens[] = new Department($child['department_id']);
		}

		return $childrens;
	}

	public function setPermission($permissionName, $permissionValue)
	{
		$permission = Database::query("SELECT * FROM `departments_permissions` WHERE `name` = ?", [strval($permissionName)], Database::SINGLE);
		if(empty($permission))return false;

		if($permissionValue == true && $this->getPermission($permissionName) == false)
			Database::query("INSERT INTO `departments_permitted` (`department_id`, `permission_id`)VALUES(?, ?)", 
				[intval($this->get('department_id')), intval($permission['permission_id'])]);
		else if($permissionValue == false)
			Database::query("DELETE FROM `departments_permitted` WHERE `department_id` = ? AND `permission_id` = ?", 
				[intval($this->get('department_id')), intval($permission['permission_id'])]);

		$this->permissions = Database::query("SELECT * FROM `departments_permitted` WHERE `department_id` = ?", [intval($this->get('department_id'))]);
	}
}