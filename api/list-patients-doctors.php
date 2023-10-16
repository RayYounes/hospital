<?php

include('dbcon.php');
require 'vendor/autoload.php'; // Include the JWT library

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$headers = getallheaders();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $response = array();

    if (isset($headers['Authorization'])) {
        // Extract the token.
        $authorizationHeader = $headers['Authorization'];
        $token = str_replace('Bearer ', '', $authorizationHeader);

        try {
            if (!empty($token)) { // Check if the token is not empty.
                // Decode and verify the JWT token.
                $decoded = JWT::decode($token, new Key('ray123', 'HS256'));
                // Use the last data
                $lastData = end($decoded);

                // For testing and checking the value.
                // echo 'Your role is a : ' . $lastData.
                // Condition only if the Role is Admin.
                if ($lastData == 'Admin') {
                    $sql = "SELECT ud.name AS doctor_name, up.name AS patient_name
                    FROM DoctorPatientMap dp
                    LEFT JOIN Users ud ON dp.doctorID = ud.userid
                    LEFT JOIN Users up ON dp.patientID = up.userid
                    WHERE dp.status = 1";

                    $result = $db->query($sql);

                    if ($result) {
                        $data = array();
                        while ($row = $result->fetch_assoc()) {
                            $data[] = $row;
                        }

                        // Close the database connection.
                        $result->close();

                        $response = $data;
                        echo json_encode($response);
                    } else {
                        // Error handling.
                        $response["error"] = "Error retrieving patient-doctor data: " . $db->error;
                        echo json_encode($response);
                    }
                } else {
                    // Error Unauthorized with response 401.
                    http_response_code(401);
                    echo "Unauthorized";
                }
            } else {
                echo "Empty JWT token"; // Add a response for an empty token.
            }
        } catch (Exception $e) {
            // An error occurred while decoding or verifying the token.
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "No JWT token found"; // Add a response for when there is no token in the headers.
    }
}
