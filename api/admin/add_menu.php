<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$caption = $_POST['caption'];
$sequence = intval($_POST['sequence']);
$active = intval($_POST['active']);
$success = FALSE;

if (!empty($caption) && !empty($sequence) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'INSERT INTO menu (caption, sequence, active)' .
		'         VALUES (:caption, :sequence, :active)';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':caption', $caption, PDO::PARAM_STR);
		$statement->bindParam(':sequence', $sequence, PDO::PARAM_INT);
		$statement->bindParam(':active', $active, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Menu zostało poprawnie zapisane.';
			$success = true;
		} 
		else {
			$message = 'Menu nie zostało zapisane.';
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
