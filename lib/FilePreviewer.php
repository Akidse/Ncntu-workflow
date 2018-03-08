<?php

class FilePreviewer
{
	private $file;
	public function __construct($file)
	{
		$this->file = $file;
	}

	public function displayWord($path)
	{
		$url = 'http://docs.google.com/gview?url='.PathManager::site($path).'&embedded=true';
		echo '<iframe width="100%" height="100%" src="'.$url.'" frameborder="0"></iframe>';
	}

	public function displayPDF($path)
	{
		$url = 'http://docs.google.com/gview?url='.PathManager::site($path).'&embedded=true';
		echo '<iframe width="100%" height="100%" src="'.$url.'" frameborder="0"></iframe>';
	}

	public function displayImage($path)
	{
		echo '<img class="previewer-image" src="'.$path.'"/>';
	}
	public function display()
	{
		$path = $this->file->getPath(true);
		switch($this->file->getFormat())
		{
			case 'docx':case 'doc':
				$this->displayWord($path);
			break;
			case 'pdf':
				$this->displayPDF($path);
			break;
			default:
			$this->displayImage($path);
			break;
		}
	}
}