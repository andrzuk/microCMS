<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = intval($_POST['id']);
$login = $_POST['login'];
$email = $_POST['email'];
$role = intval($_POST['role']);
$active = intval($_POST['active']);
$success = FALSE;

if (!empty($id) && !empty($login) && !empty($email) && !empty($role) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = ADMIN;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'UPDATE users' .
		'         SET login = :login, email = :email, role = :role, active = :active' .
		'         WHERE id = :id';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':login', $login, PDO::PARAM_STR);
		$statement->bindParam(':email', $email, PDO::PARAM_STR);
		$statement->bindParam(':role', $role, PDO::PARAM_INT);
		$statement->bindParam(':active', $active, PDO::PARAM_INT);
		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Użytkownik został poprawnie zapisany.';
			$success = true;
		} 
		else {
			$message = 'Użytkownik nie został zapisany.';
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
