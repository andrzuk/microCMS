<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = intval($_POST['id']);
$password = $_POST['password'];
$success = FALSE;

if (!empty($id) && !empty($password) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = ADMIN;
	
	if (check_access($required_level, $token, $db_connection)) {

		$password = sha1($password);

		$query = 'UPDATE users' .
		'         SET password = :password' .
		'         WHERE id = :id';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':password', $password, PDO::PARAM_STR);
		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Hasło zostało poprawnie zapisane.';
			$success = true;
		} 
		else {
			$message = 'Hasło nie zostało zapisane.';
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
