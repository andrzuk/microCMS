<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$result = array();
$success = FALSE;

if (!empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;

	if (check_access($required_level, $token, $db_connection)) {
		
		$query = 'SELECT id, name, content FROM parts' .
		'         WHERE id = :id';

		$statement = $db_connection->prepare($query);

		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		
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
