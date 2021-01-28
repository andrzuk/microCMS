<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = intval($_POST['id']);
$name = $_POST['name'];
$content = $_POST['content'];
$success = FALSE;

if (!empty($id) && !empty($name) && !empty($content) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'UPDATE parts' .
		'         SET name = :name, content = :content' .
		'         WHERE id = :id';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':name', $name, PDO::PARAM_STR);
		$statement->bindParam(':content', $content, PDO::PARAM_STR);
		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Rozdział został poprawnie zapisany.';
			$success = true;
		} 
		else {
			$message = 'Rozdział nie został zapisany.';
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
