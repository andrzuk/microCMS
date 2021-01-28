<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include dirname(__FILE__) . '/../db/connection.php';

$db_connection = connect();

$query = 'SELECT * FROM sections' .
'         WHERE active = 1' .
'         ORDER BY sequence';

$statement = $db_connection->prepare($query);

$statement->execute();

$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

echo json_encode (
	array (
		'data' => $rows, 
		'status' => 200,
	)
);

?>
