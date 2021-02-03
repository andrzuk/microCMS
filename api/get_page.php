<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include dirname(__FILE__) . '/../db/connection.php';

$db_connection = connect();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = 'SELECT id, page_index, title, content, modified FROM pages' .
'         WHERE id = :id';

$statement = $db_connection->prepare($query);

$statement->bindParam(':id', $id, PDO::PARAM_INT);

$statement->execute();

$row = $statement->fetch(PDO::FETCH_ASSOC);

echo json_encode (
	array (
		'data' => $row, 
		'status' => 200,
	)
);

?>
