<?php

class SessionAlerts
{
	public $alerts 	   = array('error' => array(),
							'warning' => array(),
							'info' => array(),
							'success' => array(),
					);
	public $alertsHtml = array('error' => '<div class="alert alert-danger">%s</div>',
							'warning' => '<div class="alert alert-warning">%s</div>',
							'info' => '<div class="alert alert-info">%s</div>', 
							'success' => '<div class="alert alert-success">%s</div>', 
						);

	public function __construct($config = null)
	{
		if(isset($config['errorHtml']))$this->setHtmlDisplay($config('errorHtml'), 'error');
		if(isset($config['warningHtml']))$this->setHtmlDisplay($config('warningHtml'), 'warning');
		if(isset($config['infoHtml']))$this->setHtmlDisplay($config('infoHtml'), 'info');
		if(isset($config['successHtml']))$this->setHtmlDisplay($config('successHtml'), 'succes');

		if(!isset($_SESSION['alerts']))$_SESSION['alerts'] = $this->alerts;
	}

	public function setHtmlDisplay($html, $alertType)
	{
		if(isset($this->alertsHtml[$alertType]))
		{
			$this->alertsHtml[$alertType] = $html;
		}
	}


	public function display()
	{
		foreach($_SESSION['alerts'] as $alertType => $alertArray)
		{
			foreach($alertArray as $alertMessage)
			{
				printf($this->alertsHtml[$alertType], $alertMessage);
			}
		}
		$_SESSION['alerts'] = $this->alerts;
	}

	public function getAlertsList($alertType = null)
	{
		if($alertType == null)return $this->alerts;
		else if(isset($this->alerts[$alertType]))return $_SESSION['alerts'][$alertType];
		else return -1;
	}

	public function add($message, $alertType)
	{
		if(isset($this->alerts[$alertType]))
		{
			$_SESSION['alerts'][$alertType][] = $message;
		}
	}
}