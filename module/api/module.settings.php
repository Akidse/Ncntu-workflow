<?php
//header("Access-Control-Allow-Orgin: *");
//header("Access-Control-Allow-Methods: *");
//header("Content-Type: application/json");

$headers = getallheaders();
/*
if(!isset($headers['x-access-token']) || $headers['x-access-token'] != Config::get('api_token'))
{
	die(json_encode(['error' => 'authorization failed']));
}
*/