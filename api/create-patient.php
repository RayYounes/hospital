<?php
include('dbcon.php');
require 'vendor/autoload.php'; // Include the JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['email'])) {
    //Retrieve data from POST method.
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    if (isset($headers['Authorization'])) {
        // Extract the token.
        $authorizationHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authorizationHeader);

        if (!empty($token)) {

            try {
                // Decode and verify the JWT token.
                $decoded = JWT::decode($token, new Key('ray123', 'HS256'));
                $lastData = end($decoded);

                // To test the output
                // echo 'Your role is a : ' . $lastData;


                //Making condition if Admin to execute the query.
                if ($lastData == 'Admin') {
                    //hash the password for better security.
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert a new patient into the Users and Patients tables.
                    $sql = "INSERT INTO Users (username, passwordHash, userType, name, email) VALUES (?, ?, 'Patient', ?, ?)";
                    $stmt = $db->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param('ssss', $username, $passwordHash, $name, $email);

                        if ($stmt->execute()) {
                            $patientID = $stmt->insert_id;
                            // Insert patient ID into the table Patients.
                            $sqlPatient = "INSERT INTO Patients (patientID) VALUES (?)";
                            $stmtPatient = $db->prepare($sqlPatient);

                            if ($stmtPatient) {
                                $stmtPatient->bind_param('i', $patientID);

                                if ($stmtPatient->execute()) {
                                    // Successfully created the patient.
                                    echo "Patient created successfully.";
                                } else {
                                    // Error handling for patient-specific information.
                                    echo "Error creating patient: " . $stmtPatient->error;
                                }
                            } else {
                                // Error handling for preparing the patient-specific statement.
                                echo "Error creating patient: " . $db->error;
                            }
                        } else {
                            // Error handling for the first statement execution.
                            echo "Error creating patient: " . $stmt->error;
                        }
                    } else {
                        // Error handling for preparing the statement.
                        echo "Error creating patient: " . $db->error;
                    }
                } else {
                    //Error Unauthorized and error 401.
                    http_response_code(401);
                    echo "Unauthorized";
                }
            } catch (Exception $e) {
                // An error occurred while decoding or verifying the token.
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Empty JWT token"; // Output for an empty or missing token.
        }
    } else {
        echo "Empty JWT token"; // Output for an empty or missing token.
    }
}
