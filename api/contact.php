<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include dirname(__FILE__) . '/../db/connection.php';

$email_sender_name = MAIL_SENDER_NAME;
$email_sender_address = MAIL_SENDER_ADDRESS;

$name = $_POST['name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$user_message = $_POST['message'];
$success = FALSE;

if (!empty($name) && !empty($email) && !empty($phone) && !empty($user_message)) {
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		
		$db_connection = connect();

		$query = 'INSERT INTO messages (name, email, phone, content, sent)' .
		'         VALUES (:name, :email, :phone, :content, NOW())';

		$statement = $db_connection->prepare($query);
		$statement->bindParam(':name', $name, PDO::PARAM_STR);
		$statement->bindParam(':email', $email, PDO::PARAM_STR);
		$statement->bindParam(':phone', $phone, PDO::PARAM_STR);
		$statement->bindParam(':content', $user_message, PDO::PARAM_STR);

		$statement->execute();

		// wysyła e-mailem kopie wiadomosci do autora:
		$recipient = $email;
		$mail_body = "Drogi Użytkowniku,\n\nPodając się jako {".$name."} napisałe(a)ś do serwisu wiadomość:\n\n\"".$user_message."\"\n\nBardzo dziękujemy. Wiadomość zostanie niezwłocznie rozpatrzona.\n\n";
		$subject = "Contact Form Message";
		$header = "From: ". $email_sender_name . " <" . $email_sender_address . ">\r\n";
		$header = "MIME-Versio: 1.0\r\n" . "Content-type: text/html; charset=UTF-8\r\n" . $header;
		$mail_body = convert_to_html($subject, $mail_body);
		mail($recipient, $subject, $mail_body, $header);
		
		if ($statement->rowCount()) {
			$message = 'Wiadomość została poprawnie zapisana.';
			$success = true;
		} 
		else {
			$message = 'Wiadomość nie została zapisana.';
			$success = false;
		}
	}
	else {
		$message = 'Adres email jest nieprawidłowy.';
		$success = false;
	}
}
else {
	$message = 'Nie podano wszystkich wymaganych danych.';
	$success = false;
}

echo json_encode (
	array (
		'result' => $success, 
		'message' => $message,
	)
);

function convert_to_html($subject, $content) {
	$result = "<html><head><title>" . $subject . "</title></head><body><p>" . $content . "</p></body></html>";
	$result = str_replace("\n", "<br />", $result);
	return $result;
}

?>
