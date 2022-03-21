<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: sequence-Type, X-Auth-Token");

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$id = intval($_POST['id']);
$page_index = $_POST['page_index'];
$title = $_POST['title'];
$content = $_POST['content'];
$success = FALSE;

if (!empty($id) && !empty($page_index) && !empty($title) && !empty($content) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;
	
	if (check_access($required_level, $token, $db_connection)) {

		$query = 'UPDATE pages' .
		'         SET page_index = :page_index, title = :title, content = :content, modified = NOW()' .
		'         WHERE id = :id';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':page_index', $page_index, PDO::PARAM_STR);
		$statement->bindParam(':title', $title, PDO::PARAM_STR);
		$statement->bindParam(':content', $content, PDO::PARAM_STR);
		$statement->bindParam(':id', $id, PDO::PARAM_INT);

		$statement->execute();
		
		if ($statement->rowCount()) {
			$message = 'Strona została poprawnie zapisana.';
			$success = true;
		} 
		else {
			$message = 'Strona nie została zapisana.';
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
