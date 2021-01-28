<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$login = $_POST['login'];
$email = $_POST['email'];
$role = intval($_POST['role']);
$active = intval($_POST['active']);
$success = FALSE;

if (!empty($login) && !empty($email) && !empty($role) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = ADMIN;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'SELECT COUNT(*) AS counter FROM users' .
		'         WHERE login = :login OR email = :email';

		$statement = $db_connection->prepare($query);

		$statement->bindParam(':login', $login, PDO::PARAM_STR);
		$statement->bindParam(':email', $email, PDO::PARAM_STR);

		$statement->execute();
		
		$row = $statement->fetch(PDO::FETCH_ASSOC);
		
		if ($row['counter'] == 0) {

			$query = 'INSERT INTO users (login, email, role, active)' .
			'         VALUES (:login, :email, :role, :active)';

			$statement = $db_connection->prepare($query);
			$statement->bindParam(':login', $login, PDO::PARAM_STR);
			$statement->bindParam(':email', $email, PDO::PARAM_STR);
			$statement->bindParam(':role', $role, PDO::PARAM_INT);
			$statement->bindParam(':active', $active, PDO::PARAM_INT);

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
			$message = 'Podany Login lub Email już występuje.';
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
