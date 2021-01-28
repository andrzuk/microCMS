<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include dirname(__FILE__) . '/../db/connection.php';

$db_connection = connect();

$part = $_GET['name'];

$query = 'SELECT * FROM parts' .
'         WHERE name = :part';

$statement = $db_connection->prepare($query);

$statement->bindParam(':part', $part, PDO::PARAM_STR);

$statement->execute();

$row = $statement->fetch(PDO::FETCH_ASSOC);

echo json_encode (
	array (
		'data' => $row, 
		'status' => 200,
	)
);

?>
