<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include dirname(__FILE__) . '/../db/connection.php';

$db_connection = connect();

$schema = DB_NAME;

$query = 'SELECT TABLE_NAME AS name FROM INFORMATION_SCHEMA.TABLES' .
'         WHERE TABLE_SCHEMA = :schema';

$statement = $db_connection->prepare($query);

$statement->bindParam(':schema', $schema, PDO::PARAM_STR);

$statement->execute();

$rows = $statement->fetchAll(PDO::FETCH_ASSOC);

$app_tables = array('roles', 'users', 'menu', 'sections', 'parts', 'images');

$result = count($rows) == count($app_tables);

foreach ($rows as $row) {
	if (!in_array($row['name'], $app_tables)) {
		$result = FALSE;
	}
}

echo json_encode (
	array (
		'result' => $result, 
		'status' => 200,
	)
);

?>
