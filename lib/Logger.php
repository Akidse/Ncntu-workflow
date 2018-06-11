<?php

class Logger
{
	private $logFileName = null;

	public function __construct()
	{
		$this->logFileName = PathManager::logs('logs-'.date('Y').'-'.date('g'));
	}

	public function write($message, $user = null, $timestamp = true)
	{
		if($user != null)$message = 'User[id='.$user->get('user_id').'] action - '.$message;
		if($timestamp)$message = date("Y-m-d H:i:s").' - '.$message;
		$logFile = fopen($this->logFileName, "a") or die("Unable to open log file!");
		fwrite($logFile, $message."\r\n");
		fclose($logFile);
	}
}