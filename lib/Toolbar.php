<?php

class Toolbar
{
	private $buttonsArray = array();

	public function addButton($url = "/", $name = "Button", $class = 'btn-primary')
	{
		$this->buttonsArray[] = array('url' => $url, 'name' => $name, 'class' => $class);
	}

	public function display()
	{
		if(count($this->buttonsArray) == 0)return;

		$html = '<div class="toolbar">';
		foreach($this->buttonsArray as $button)
		{
			$html.= '<a href="'.$button['url'].'" class="btn mr-2 '.$button['class'].'">'.$button['name'].'</a>';
		}
		$html.= '</div>';

		echo $html;
	}
}