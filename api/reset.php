<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

include dirname(__FILE__) . '/../db/connection.php';

$email_sender_name = MAIL_SENDER_NAME;
$email_sender_address = MAIL_SENDER_ADDRESS;

$id = 0;

$email = $_GET['email'];
$token = $_GET['token'];

if (!empty($email) && !empty($token)) {
	
	$db_connection = connect();

	$query = 'SELECT * FROM users' .
	'         WHERE email = :email AND token = :token' .
	'         AND active = 1';

	$statement = $db_connection->prepare($query);

	$statement->bindParam(':email', $email, PDO::PARAM_STR);
	$statement->bindParam(':token', $token, PDO::PARAM_STR);
	
	$statement->execute();
	
	$row_item = $statement->fetch(PDO::FETCH_ASSOC);
	
	if (is_array($row_item)) {
		
		if (array_key_exists('id', $row_item)) {
			
			$id = $row_item['id'];
			$phrase = NULL;
			$length = 8;
			$code = md5(uniqid(rand(), true));
			$phrase = substr($code, 0, $length);
			$password = sha1($phrase);

			$query = 'UPDATE users' .
			'         SET password = :password' .
			'         WHERE id = :id';

			$statement = $db_connection->prepare($query);

			$statement->bindParam(':password', $password, PDO::PARAM_STR);
			$statement->bindParam(':id', $id, PDO::PARAM_INT);

			$statement->execute();
			
			// wysyła e-mailem nowe hasło logowania:
			$recipient = $email;
			$mail_body = "Drogi Użytkowniku,\n\nNowe hasło logowania do Panelu Administratora jest następujące:\n\n<b>".$phrase."</b>\n\nZaloguj się tym hasłem, a następnie w Panelu Administratora zmień je na swoje własne.\n\n";
			$subject = "Your New Password Message";
			$header = "From: ". $email_sender_name . " <" . $email_sender_address . ">\r\n";
			$header = "MIME-Versio: 1.0\r\n" . "Content-type: text/html; charset=UTF-8\r\n" . $header;
			$mail_body = convert_to_html($subject, $mail_body);
			mail($recipient, $subject, $mail_body, $header);
			
			header('Location: '. DOMAIN_URL .'/admin');
		}
	}
	else {
		header('Location: '. DOMAIN_URL);
	}
}
else {
	header('Location: '. DOMAIN_URL);
}

function convert_to_html($subject, $content) {
	$result = "<html><head><title>" . $subject . "</title></head><body><p>" . $content . "</p></body></html>";
	$result = str_replace("\n", "<br />", $result);
	return $result;
}

?>
