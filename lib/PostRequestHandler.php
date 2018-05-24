<?php

class PostRequestHandler
{
	public const STRING_TYPE = 1;
	public const BOOL_TYPE = 2;
	public const INT_TYPE = 3;

	public const STRLEN_VALID = 1;
	public const EMAIL_VALID = 2;
	public const NUMBER_VALID = 3;

	private $fields = array();
	private $validators = array();

	private $isValid = true;

	public function __construct(array $config)
	{
		if(isset($config['fields']))$this->fields = $config['fields'];
		if(isset($config['validators']))$this->validators = $config['validators'];
	}

	public function addInputs(array $inputs)
	{
		if(isset($config['fields']))$this->fields = array_merge($this->fields, $input['fields']);
		if(isset($config['validators']))$this->validators = array_merge($this->validators, $input['validators']);
	}

	public function proceed()
	{
		if(!isset($_POST) || $_POST == null)return false;
		foreach($this->fields as $field => $params)
		{
			$isValid = true;

			if(isset($params['required']) && $params['required'] == true && !isset($_POST[$field]))$isValid = false;

			if(isset($_POST[$field]) && $this->validate($field) == false)$isValid = false;

			$this->fields[$field]['is_valid'] = $isValid;

			if(!$isValid)$this->isValid = false;
		}

		return true;
	}

	private function validate(string $field)
	{
		if(!isset($this->validators[$field]))return true;

		$fieldValue = $_POST[$field];
		$isValid = true;
		foreach($this->validators[$field] as $validatorType => $params)
		{
			switch($validatorType)
			{
				case self::STRLEN_VALID:
					if(isset($params['min']) && strlen($fieldValue) < $params['min'])$isValid = false;
					if(isset($params['max']) && strlen($fieldValue) > $params['max'])$isValid = false;
				break;
				case self::NUMBER_VALID:
				
				break;
				default:
				$isValid = true;
				break;
			}
		}

		return $isValid;
	}

	private function normalize(string $field)
	{
		if(!isset($this->fields[$field]['type']))return $_POST[$field];
		if(empty($_POST[$field]))return $_POST[$field];

		switch($this->fields[$field]['type'])
		{
			case self::STRING_TYPE:
				return strval($_POST[$field]);
			break;
			case self::BOOL_TYPE:
				return boolval($_POST[$field]);
			break;
			case self::INT_TYPE:
				return intval($_POST[$field]);
			break;
			default:
				return $_POST[$field];
			break;
		}
	}

	public function get(string $field)
	{
		if(isset($_POST[$field]))return $this->normalize($field);
		else if(isset($this->fields[$field]['default']))return $this->fields[$field]['default'];
		else return null;
	}

	public function isValid(string $field = null)
	{
		if($field != null && isset($this->fields[$field]))
			return $this->fields[$field]['is_valid'];
		
		return $this->isValid;
	}

	public function setValid(bool $value)
	{
		$this->isValid = boolval($value);
	}
}