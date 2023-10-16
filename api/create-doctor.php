<?php
include('dbcon.php');
require 'vendor/autoload.php'; // Include the JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['name']) && isset($_POST['email'])) {
    //Retrieve data from POST method.
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    if (isset($headers['Authorization'])) {
        // Extract the token.
        $authorizationHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authorizationHeader);

        try {
            if (!empty($token)) { // Check if the token is not empty.
                // Decode and verify the JWT token.
                $decoded = JWT::decode($token, new Key('ray123', 'HS256'));
                // Use the last data since userType was placed at the end.
                $lastData = end($decoded);

                // To test the output
                // echo 'Your role is a : ' . $lastData;


                //Making condition if Admin to execute the query.
                if ($lastData == 'Admin') {
                    //hash the password for better security.
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                    // Insert a new doctor into the Users and Doctors tables.
                    $sql = "INSERT INTO Users (username, passwordHash, userType, name, email) VALUES (?, ?, 'Doctor', ?, ?)";
                    $stmt = $db->prepare($sql);

                    if ($stmt) {
                        $stmt->bind_param('ssss', $username, $passwordHash, $name, $email);

                        if ($stmt->execute()) {
                            $doctorID = $stmt->insert_id;
                            // Insert doctors' extra information into the Doctors table.
                            $sqlDoctor = "INSERT INTO Doctors (doctorID, specialization, availability) VALUES (?, ?, ?)";
                            $stmtDoctor = $db->prepare($sqlDoctor);

                            if ($stmtDoctor) {
                                $stmtDoctor->bind_param('iss', $doctorID, $_POST['specialization'], $_POST['availability']);

                                if ($stmtDoctor->execute()) {
                                    // Successfully created the doctor.
                                    echo "Doctor created successfully.";
                                } else {
                                    // Error handling for doctor-specific information.
                                    echo "Error creating doctor: " . $stmtDoctor->error;
                                }
                            } else {
                                // Error handling for preparing the doctor-specific statement.
                                echo "Error creating doctor: " . $db->error;
                            }
                        } else {
                            // Error handling for the first statement execution.
                            echo "Error creating doctor: " . $stmt->error;
                        }
                    } else {
                        // Error handling for preparing the statement.
                        echo "Error creating doctor: " . $db->error;
                    }
                } else {
                    //Output Unauthorized and response code of 401.
                    http_response_code(401);
                    echo "Unauthorized";
                }
            } else {
                echo "Empty JWT token"; // Output for an empty token.
            }
        } catch (Exception $e) {
            // An error occurred while decoding or verifying the token.
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Empty JWT token"; // Output for an empty token.
    }
}
