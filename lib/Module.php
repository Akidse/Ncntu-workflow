<?php

class Module
{
	const _PUBLIC = 0;
	const _API = 1;
	const _ADMIN = 2;
	const _PANEL = 3;
	public static $types = ['', 'api', 'admin', 'panel'];

	private $type = 1;
	private $moduleName;

	public function __construct($moduleName, $type = self::_PUBLIC)
	{
		$this->moduleName = $moduleName;
		$this->type = $type;
	}

	public function getName()
	{
		return $this->moduleName;
	}

	public function getType()
	{
		return $this->type;
	}

	public function getPath()
	{
		return PathManager::module($this);
	}

	public function getTypeName()
	{
		return self::$types[$this->type];
	}


	public function getFullName()
	{
		$typeName = ($this->getTypeName() != null? $this->getTypeName().'/':null);
		return $typeName.$this->getName();
	}
}