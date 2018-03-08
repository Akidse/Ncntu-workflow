<?php

class FileHelper
{
	public static function generateRandomName($extension)
	{
		return uniqid(). '.'.$extension;
	}

	public static function diverseFileArray($fileArray)
	{
	    $resultArray = array();
	    foreach($fileArray as $key1 => $value1)
	        foreach($value1 as $key2 => $value2)
	            $resultArray[$key2][$key1] = $value2;
	    return $resultArray;
	}


	public static function removeFileById($id)
	{
		$file = Database::query("SELECT * FROM `files` WHERE `file_id` = ?", [intval($id)], Database::SINGLE);
		if(!empty($file))
		{
			unlink(PathManager::file($file['real_name']));
			Database::query("DELETE FROM `files` WHERE `file_id` = ?", [intval($id)]);			
		}
	}
}