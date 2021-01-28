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

	$required_level = OPERATOR;

	if (check_access($required_level, $token, $db_connection)) {

		$query = 'SELECT COUNT(*) AS counter FROM sections' .
		'         WHERE menu_id = :menu_id';

		$statement = $db_connection->prepare($query);

		$statement->bindParam(':menu_id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		
		if ($row['counter'] == 0) {

			$query = 'DELETE FROM menu' .
			'         WHERE id = :id';

			$statement = $db_connection->prepare($query);

			$statement->bindParam(':id', $id, PDO::PARAM_INT);

			$statement->execute();
			
			if ($statement->rowCount()) {
				$message = 'Menu zostało poprawnie usunięte.';
				$success = true;
			} 
			else {
				$message = 'Menu nie zostało usunięte.';
				$success = false;
			}
		}
		else {
			$message = 'Nie można usunąć menu, z którym powiązana jest sekcja.';
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
