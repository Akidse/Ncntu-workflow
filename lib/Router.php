<?php

class Router
{
	private $currentRoute;
	private $action;
	private $page;
	private $module;
	private $params;
	private $indexPage = 'index';
	private $availableForGuests = false;

	public function __construct($pRoute = null)
	{
		if($pRoute == null)
		{
			$this->currentRoute = $_SERVER['REQUEST_URI'];
			if($this->currentRoute == '/')$this->currentRoute = $this->indexPage;
		}
		else
		{
			$this->currentRoute = $pRoute;
		}

		$this->parseRoute();
	}

	private function parseRoute()
	{
		$routeToParse = trim($this->currentRoute, '/');
		$routeArray = explode('/', $routeToParse);

		$moduleType = array_shift($routeArray);
		$this->page = $moduleType;
		if(!in_array($moduleType, Module::$types))$this->module = new Module($this->page);
		else
		{
			$this->page = array_shift($routeArray);
			if($this->page == null)$this->page = $this->indexPage;
			$this->module = new Module($this->page, array_search($moduleType, Module::$types));
		}
		$this->action = array_shift($routeArray);
		$this->params = $routeArray;
		if(in_array($this->module->getFullName(), explode(",", Config::get('guest_pages'))))
		{
			$this->availableForGuests = true;
		}
	}

	public function path($moduleName)
	{
		return "/".$moduleName."/";
	}
	public function redirect($module)
	{
		switch(gettype($module))
		{
			case 'object':
				if(!PathManager::module($module))return false;
				Header("Location: /".$module->getName()."/");
			break;
			case 'string':
				$module = trim($module, '/');
				Header("Location: /".$module);
			break;
		}
		die();
	}

	public function getRoute()
	{
		return $this->currentRoute;
	}
	public function isAvailableForGuests()
	{
		return $this->availableForGuests;
	}
	public function setIndexPage($page)
	{
		$this->indexPage = $page;
	}
	public function getModule()
	{
		return $this->module;
	}
	public function getPage()
	{
		return $this->page;
	}

	public function getAction()
	{
		return (!empty($this->action)?$this->action:null);
	}

	public function getParams($index = -1)
	{
		if(is_int($index))
		{
			if(empty($this->params) || empty($this->params[$index]))return null;
			if($index > -1)return $this->params[$index];
			return $this->params;			
		}
		else if(is_string($index))
		{
			if(array_search($index, $this->params) !== false && count($this->params) > array_search($index, $this->params))
			{
				return $this->params[array_search($index, $this->params) + 1];
			}
		}

		return null;
	}

	public function url($actionParams = null, $module = null)
	{
		$actionParams = trim($actionParams, '/');
		if(gettype($module) == 'object')$module = $module->getFullName();
		if($module == null)$module = $this->module->getFullName();
		
		return '/'.$module.'/'.$actionParams;
	}
}