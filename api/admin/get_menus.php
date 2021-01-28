<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$result = array();
$success = FALSE;

if (!empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;

	if (check_access($required_level, $token, $db_connection)) {
		
		$query = 'SELECT id, caption, sequence, active FROM menu' .
		'         ORDER BY id';

		$statement = $db_connection->prepare($query);

		$statement->execute();
		
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		
		$success = TRUE;
	}
}

echo json_encode (
	array (
		'result' => $result,
		'success' => $success
	)
);

?>
