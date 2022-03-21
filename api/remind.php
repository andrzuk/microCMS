<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include dirname(__FILE__) . '/../db/connection.php';

$email_sender_name = MAIL_SENDER_NAME;
$email_sender_address = MAIL_SENDER_ADDRESS;

$id = 0;
$token = NULL;
$success = FALSE;
$message = NULL;

$email = $_POST['email'];

if (!empty($email)) {
	
	$db_connection = connect();

	$query = 'SELECT * FROM users' .
	'         WHERE email = :email' .
	'         AND active = 1';

	$statement = $db_connection->prepare($query);

	$statement->bindParam(':email', $email, PDO::PARAM_STR);
	
	$statement->execute();
	
	$row_item = $statement->fetch(PDO::FETCH_ASSOC);
	
	if (is_array($row_item)) {
		
		if (array_key_exists('id', $row_item)) {
			
			$id = $row_item['id'];
			$token = hash('sha256', uniqid());
			$reset_link = DOMAIN_URL . '/api/reset.php?email=' . $email . '&token=' . $token;
			$success = true;
			$message = 'Link do odzyskania hasła został wysłany na Twoją skrzynkę email.';

			$query = 'UPDATE users' .
			'         SET token = :token' .
			'         WHERE id = :id';

			$statement = $db_connection->prepare($query);

			$statement->bindParam(':token', $token, PDO::PARAM_STR);
			$statement->bindParam(':id', $id, PDO::PARAM_INT);

			$statement->execute();
			
			// wysyła e-mailem link do resetu hasła:
			$recipient = $email;
			$mail_body = "Drogi Użytkowniku,\n\nWysłałe(a)ś do serwisu żądanie odzyskania hasła. Aby wygenerować nowe hasło, kliknij w poniższy link:\n\n<a href=\"".$reset_link."\">".$reset_link."</a>\n\nW kolejnym mailu otrzymasz nowe hasło do Panelu Administratora.\n\n";
			$subject = "Reset Password Message";
			$header = "From: ". $email_sender_name . " <" . $email_sender_address . ">\r\n";
			$header = "MIME-Versio: 1.0\r\n" . "Content-type: text/html; charset=UTF-8\r\n" . $header;
			$mail_body = convert_to_html($subject, $mail_body);
			mail($recipient, $subject, $mail_body, $header);
		}
	}
	else {
		$message = 'Email jest nieprawidłowy.';
	}
}
else {
	$message = "Nie podano wszystkich wymaganych danych.";
}

echo json_encode (
	array (
		'success' => $success, 
		'message' => $message,
	)
);

function convert_to_html($subject, $content) {
	$result = "<html><head><title>" . $subject . "</title></head><body><p>" . $content . "</p></body></html>";
	$result = str_replace("\n", "<br />", $result);
	return $result;
}

?>
