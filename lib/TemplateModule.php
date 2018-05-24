<?php

class TemplateModule
{
	private $data = array();
	private $templateName = null;

	public function __construct($templateName)
	{
		if(!empty(PathManager::templateModule(trim($templateName, '\/'))))
			$this->templateName = $templateName;
		else die("There is TemplateModule Error!!!");

	}


	public function display($data = null)
	{
		global $router;
		
		if(!empty($data))
			$this->data = $data;

		include(PathManager::templateModule(trim($this->templateName, '\/')));
	}
}