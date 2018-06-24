<?php
print_r(getallheaders());
switch($_SERVER['REQUEST_METHOD'])
{
	case 'GET':
	$decrees = Database::query("SELECT * FROM `decrees` ORDER BY `decree_id` DESC");
	echo json_encode($decrees);
	break;
	case 'PUT':

	break;
	case 'POST':

	break;
	case 'DELETE':

	break;
}