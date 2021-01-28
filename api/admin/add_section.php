<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$menu_id = intval($_POST['menu_id']);
$content = $_POST['content'];
$sequence = intval($_POST['sequence']);
$active = intval($_POST['active']);
$success = FALSE;

if (!empty($menu_id) && !empty($content) && !empty($sequence) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'SELECT COUNT(*) AS counter FROM menu' .
		'         WHERE id = :menu_id';

		$statement = $db_connection->prepare($query);

		$statement->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);

		$statement->execute();
		
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		
		if ($row['counter'] > 0) {

			$query = 'INSERT INTO sections (menu_id, content, sequence, active)' .
			'         VALUES (:menu_id, :content, :sequence, :active)';
	
			$statement = $db_connection->prepare($query);
			$statement->bindParam(':menu_id', $menu_id, PDO::PARAM_INT);
			$statement->bindParam(':content', $content, PDO::PARAM_STR);
			$statement->bindParam(':sequence', $sequence, PDO::PARAM_INT);
			$statement->bindParam(':active', $active, PDO::PARAM_INT);
	
			$statement->execute();
			
			if ($statement->rowCount()) {
				$message = 'Sekcja została poprawnie zapisana.';
				$success = true;
			} 
			else {
				$message = 'Sekcja nie została zapisana.';
				$success = false;
			}
		}
		else {
			$message = 'Identyfikator MenuId jest nieprawidłowy.';
			$success = false;
		}
	}
	else {
		$message = 'Nie posiadasz wystarczających uprawnień.';
		$success = FALSE;	
	}
} 
else {
	$message = 'Nie podano wszystkich wymaganych danych.';
	$success = false;
}

echo json_encode (
	array (
		'success' => $success,
		'message' => $message,
	)
);

?>
