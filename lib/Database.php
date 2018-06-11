<?php

class Database
{
	private static $connection;

	const FETCH_ALL = 1;
	const FETCH_COLUMN = 2;
	const INSERT = 3;
	const SINGLE = 4;

	private static $limit = array();

	public static function connect($db_host, $db_user, $db_password, $db_name)
	{
		self::$connection = new PDO('mysql:host='.$db_host.';dbname='.$db_name.';charset=utf8', $db_user, $db_password);
	}

	public static function setNextLimit($start, $end = null)
	{
		self::$limit['start'] = intval($start);
		if(!empty($end))self::$limit['end'] = intval($end);
	}
	public static function query($query, $params = [], $query_type = null)
	{
		if(!empty(self::$limit))
		{
			if(count(self::$limit) > 1)$query.=" LIMIT ?, ?";
			else $query.=" LIMIT ?, ?";
		}
		$stmt = self::$connection->prepare($query);
		foreach($params as $key => $value)
		{
			$stmt->bindValue($key+1, $value);
		}
		if(!empty(self::$limit))
		{
			$stmt->bindValue(count($params)+1, self::$limit['start'], PDO::PARAM_INT);
			if(count(self::$limit) > 1)
				$stmt->bindValue(count($params)+2, self::$limit['end'], PDO::PARAM_INT);
			self::$limit = array();
		}

		$stmt->execute();

		if(stripos($query, 'insert') !== false && $query_type == null)$query_type = self::INSERT;
		if(stripos($query, 'count') !== false && $query_type == null)$query_type = self::FETCH_COLUMN;
		switch($query_type)
		{
			case self::FETCH_ALL:
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			break;
			case self::FETCH_COLUMN: 
				return $stmt->fetchColumn();
			break;
			case self::INSERT:
				return self::$connection->lastInsertId(); 
			break;
			case self::SINGLE:
				$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
				return ($result != null ?$result[0]:null);
				//return ($stmt->fetchAll(PDO::FETCH_ASSOC)[0]?$stmt->fetchAll(PDO::FETCH_ASSOC)[0]:null);
			break;
			case null:
				return $stmt->fetchAll(PDO::FETCH_ASSOC);
			break;
		}
	}
}