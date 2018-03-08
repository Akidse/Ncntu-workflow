<?php

class ScriptManager
{
	private $scriptsArray = array();

	public function add($scriptName)
	{
		$this->scriptsArray[] = $scriptName;
	}

	public function getScripts()
	{
		return $this->scriptsArray;
	}
}