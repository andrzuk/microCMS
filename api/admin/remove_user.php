<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$success = FALSE;

if (!empty($token)) {
	
	$db_connection = connect();

	$required_level = ADMIN;

	if (check_access($required_level, $token, $db_connection)) {

		$query = 'DELETE FROM users' .
		'         WHERE id = :id AND id <> 1';

		$statement = $db_connection->prepare($query);

		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Użytkownik został poprawnie usunięty.';
			$success = true;
		} 
		else {
			$message = 'Użytkownik nie został usunięty.';
			$success = false;
		}
	}
	else {
		$message = 'Nie posiadasz wystarczających uprawnień.';
		$success = FALSE;	
	}
}

echo json_encode (
	array (
		'message' => $message,
		'success' => $success
	)
);

?>
