<?php

include('dbcon.php');
require 'vendor/autoload.php'; // Include the JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctorID']) && isset($_POST['patientID']) && isset($_FILES['filePath']['name']) && isset($_POST['description'])) {
    //Retrieve data from POST method.
    $doctorID = $_POST['doctorID'];
    $patientID = $_POST['patientID'];
    $fileTmpPath = $_FILES['filePath']['tmp_name']; // Get the file path.
    $fileName = $_FILES['filePath']['name']; // Get the file name.
    $description = $_POST['description'];

    if (isset($headers['Authorization'])) {
        // Extract the token.
        $authorizationHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authorizationHeader);

        if (!empty($token)) { // Check if the token is not empty.
            try {
                // Decode and verify the JWT token.
                $decoded = JWT::decode($token, new key('ray123', 'HS256'));
                // Use the last data since it is the last index.
                $lastData = end($decoded);
                // For testing I did an echo.
                // echo 'Your role is a : ' . $lastData.
                // Maing a condition for the Doctor role.
                if ($lastData == 'Doctor') {

                    // Check the doctorpatientmap table for the conditions
                    $checkSql = "SELECT status FROM doctorpatientmap WHERE doctorID = ? AND patientID = ? AND status = 1";
                    $checkStmt = $db->prepare($checkSql);
                    $checkStmt->bind_param('ii', $doctorID, $patientID);
                    $checkStmt->execute();
                    $checkStmt->store_result();

                    if ($checkStmt->num_rows === 1) {
                        // The conditions are met; continue with file upload.

                        // Define the target directory where the file will be saved.
                        $targetDirectory = 'C:\wamp64\www\api\files\\';

                        // Target file path.
                        $targetFilePath = $targetDirectory . $fileName;

                        // Insert a new record into the Files table to store the file information.
                        $sql = "INSERT INTO Files (doctorID, patientID, filePath, description) VALUES (?, ?, ?, ?)";
                        $stmt = $db->prepare($sql);
                        $stmt->bind_param('ssss', $doctorID, $patientID, $fileName, $description);

                        if ($stmt->execute()) {
                            // Move the uploaded file to the target directory.
                            if (move_uploaded_file($fileTmpPath, $targetFilePath)) {
                                // Successfully uploaded the file and saved it to the folder.
                                echo "File uploaded successfully.";
                            } else {
                                // Error handling for moving the file.
                                echo "Error moving the file to the target directory.";
                            }
                        } else {
                            // Error handling for database insertion.
                            echo "Error uploading file: " . $stmt->error;
                        }
                    } else {
                        // The conditions are not met; don't proceed with file upload.
                        echo "Error: Doctor is not assigned to this patient or status is not 1.";
                    }
                } else {
                    //Error Unauthorized with the 401 response.
                    http_response_code(401);
                    echo "Unauthorized";
                }
            } catch (Exception $e) {
                // An error occurred while decoding or verifying the token.
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Empty JWT token"; // Add a response for an empty token.
        }
    } else {
        echo "No JWT token found"; // Add a response for when there is no token in the headers.
    }
}
