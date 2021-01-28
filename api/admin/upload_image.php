<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, X-Auth-Token");

header('Content-Type: image/png');
header('Content-Description: File Transfer');

include dirname(__FILE__) . '/../../db/connection.php';
include dirname(__FILE__) . '/../../db/check_access.php';

$token = get_token();

$target_dir = dirname(__FILE__) . '/../../upload/';
$target_file = $target_dir . basename($_FILES["file"]["name"]);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

$success = FALSE;

if (isset($_FILES['file']) && !empty($token)) {
	
	$db_connection = connect();

	$required_level = OPERATOR;
	
	if (check_access($required_level, $token, $db_connection)) {

		if (file_exists($target_dir)) {
			if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
				if (getimagesize($_FILES["file"]["tmp_name"])) {
					if ($_FILES["file"]["size"] < 1024 * 1024) {
						if (!file_exists($target_file)) {
							if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {

								$filename = basename($_FILES["file"]["name"]);
								$type = $_FILES["file"]["type"];
								$size = $_FILES["file"]["size"];
		
								$query = 'INSERT INTO images (filename, type, size)' .
								'         VALUES (:filename, :type, :size)';
						
								$statement = $db_connection->prepare($query);
								$statement->bindParam(':filename', $filename, PDO::PARAM_STR);
								$statement->bindParam(':type', $type, PDO::PARAM_STR);
								$statement->bindParam(':size', $size, PDO::PARAM_INT);
						
								$statement->execute();

								$query = 'SELECT MAX(id) AS id FROM images';
						
								$statement = $db_connection->prepare($query);
						
								$statement->execute();
								
								$row = $statement->fetch(PDO::FETCH_ASSOC);

								$image = array('id' => $row['id'], 'name' => $filename, 'type' => $type, 'size' => $size);
		
								$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " został zapisany.";
								$success = TRUE;
							} 
							else {
								$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " nie został zapisany.";
								$success = FALSE;
							}
						}
						else {
							$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " już istnieje.";
							$success = FALSE;
						}
					}
					else {
						$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " ma za duży rozmiar.";
						$success = FALSE;
					}
				}
				else {
					$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " ma niewłaściwy format.";
					$success = FALSE;
				}
			}
			else {
				$message = "Plik ". htmlspecialchars(basename($_FILES["file"]["name"])). " ma niewłaściwe rozszerzenie.";
				$success = FALSE;
			}
		}
		else {
			$message = 'Folder "/upload" nie istnieje.';
			$success = FALSE;	
		}
	}
	else {
		$message = 'Nie posiadasz wystarczających uprawnień.';
		$success = FALSE;	
	}
} 
else {
	$message = 'Nie podano wszystkich wymaganych danych.';
	$success = FALSE;
}

echo json_encode (
	array (
		'success' => $success,
		'message' => $message,
		'image' => $image,
	)
);

?>
