<?php

class File
{
	private $data = array();
	private $path;

	public function __construct($fileId)
	{
		$databaseFile = Database::query("SELECT * FROM `files` WHERE `file_id` = ?", [(int)$fileId], Database::SINGLE);
		if(empty($databaseFile))return;
		$this->data = $databaseFile;
		$this->path = PathManager::file($this->data['real_name']);
	}
	public function isExists()
	{
		return (!empty($this->data) && file_exists($this->path));
	}
	public function get($key)
	{
		return $this->data[$key];
	}

	public function getPath($shortUrl = false)
	{
		return PathManager::file($this->data['real_name'], $shortUrl);
	}
	public function getFormat()
	{
		$pathinfo = pathinfo($this->path);
		return strtolower($pathinfo['extension']);
	}
}