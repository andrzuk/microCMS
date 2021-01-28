<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include dirname(__FILE__) . '/../db/connection.php';

$id = 0;
$token = NULL;
$success = FALSE;
$message = NULL;

$login = $_POST['email'];
$email = $_POST['email'];
$password = $_POST['password'];

if (!empty($login) && !empty($password)) {
	
	$password = sha1($password);
	
	$db_connection = connect();

	$query = 'SELECT * FROM users' .
	'         WHERE (login = :login OR email = :login)' .
	'         AND password = :password' .
	'         AND active = 1';

	$statement = $db_connection->prepare($query);

	$statement->bindParam(':login', $login, PDO::PARAM_STR);
	$statement->bindParam(':password', $password, PDO::PARAM_STR);
	
	$statement->execute();
	
	$row_item = $statement->fetch(PDO::FETCH_ASSOC);
	
	if (is_array($row_item)) {
		
		if (array_key_exists('id', $row_item)) {
			
			$id = $row_item['id'];
			$login = $row_item['login'];
			$email = $row_item['email'];
			$logged_in = $row_item['logged_in'];
			$logged_out = $row_item['logged_out'];
			$token = hash('sha256', uniqid());
			$success = true;
			$message = 'Zostałeś poprawnie zalogowany do serwisu.';

			$query = 'UPDATE users' .
			'         SET logged_in = NOW(), token = :token' .
			'         WHERE id = :id';

			$statement = $db_connection->prepare($query);

			$statement->bindParam(':token', $token, PDO::PARAM_STR);
			$statement->bindParam(':id', $id, PDO::PARAM_INT);

			$statement->execute();
		}
	}
	else {
		$message = 'Login lub e-mail lub hasło są nieprawidłowe.';
	}
}
else {
	$message = "Nie podano wszystkich wymaganych danych.";
}

echo json_encode (
	array (
		'user' => array (
			'id' => $id, 
			'name' => $login, 
			'email' => $email, 
			'logged_in' => $logged_in, 
			'logged_out' => $logged_out, 
			'access_token' => $token, 
		), 
		'success' => $success, 
		'message' => $message,
	)
);

?>
