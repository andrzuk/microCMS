<?php

define ('ADMIN', 8);
define ('OPERATOR', 4);
define ('USER', 2);
define ('GUEST', 1);

function get_token() {
	
	$token = NULL;

	foreach (getallheaders() as $key => $value) {
		if ($key == 'X-Auth-Token') {
			$token = $value;
		}
	}
	return $token;	
}

function check_access($required_level, $token, $db_connection) {
	
	$result = false;
	$allowed = false;
	
	if ($token) {
		
		$query = 'SELECT id, role FROM users' .
		'         WHERE token = :token' .
		'         AND active = 1' .
		'         ORDER BY logged_in DESC LIMIT 1';

		$statement = $db_connection->prepare($query);

		$statement->bindValue(':token', $token, PDO::PARAM_STR); 

		$statement->execute();

		$row_item = $statement->fetch(PDO::FETCH_ASSOC);
	
		if (is_array($row_item)) {

			if (array_key_exists('role', $row_item)) {

				$role_id = $row_item['role'];

				$query = 'SELECT (mask_a * 8 + mask_o * 4 + mask_u * 2 + mask_g * 1) AS level FROM roles' .
				'         WHERE id = :role_id';
		
				$statement = $db_connection->prepare($query);
		
				$statement->bindValue(':role_id', $role_id, PDO::PARAM_INT); 
		
				$statement->execute();
		
				$row = $statement->fetch(PDO::FETCH_ASSOC);
			
				$allowed = $row['level'] >= $required_level;
			}
			if (array_key_exists('id', $row_item)) {

				$result = $row_item['id'] > 0 && $allowed;
			}
		}
	}

	return $result;
}

function get_user_by_token($token, $db_connection) {
	
	$result = false;
	
	$query = 'SELECT * FROM users WHERE token = :token';

	$statement = $db_connection->prepare($query);

	$statement->bindValue(':token', $token, PDO::PARAM_STR); 

	$statement->execute();

	$result = $statement->fetch(PDO::FETCH_ASSOC);

	return $result;
}

?>

