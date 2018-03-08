<?php

class File
{
	private $data = array();
	private $path;

	public function __construct($fileId)
	{
		$databaseFile = Database::query("SELECT * FROM `files` WHERE `file_id` = ?", [(int)$fileId], Database::SINGLE);
		$this->data = $databaseFile;
		$this->path = PathManager::file($this->data['real_name']);
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