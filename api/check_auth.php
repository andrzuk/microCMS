<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

include dirname(__FILE__) . '/../db/connection.php';
include dirname(__FILE__) . '/../db/check_access.php';

$access = FALSE;

$token = get_token();

if (!empty($token)) {
	
	$db_connection = connect();

	$query = 'SELECT id FROM users' .
	'         WHERE token = :token' .
	'         AND active = 1' .
	'         ORDER BY logged_in DESC LIMIT 1';

	$statement = $db_connection->prepare($query);

	$statement->bindParam(':token', $token, PDO::PARAM_STR);
	
	$statement->execute();
	
	$row_item = $statement->fetch(PDO::FETCH_ASSOC);
	
	if (is_array($row_item)) {
		
		if (array_key_exists('id', $row_item)) {
			
			$access = $row_item['id'] > 0;
		}
	}
}

echo json_encode (
	array (
		'success' => $access, 
	)
);

?>
