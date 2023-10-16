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
            if (!empty($token)) { // Check if the token is not empty
                // Decode and verify the JWT token.
                $decoded = JWT::decode($token, new Key('ray123', 'HS256'));
                // Use the last data.
                $lastData = end($decoded);
                // For test purpose to see the value of $lastData.
                // echo 'Your role is a : ' . $lastData;
                // Condition if Role is equal to Admin.
                if ($lastData == 'Admin') {
                    $sql = "SELECT d.doctorID, u.name AS doctor_name
                    FROM Doctors d
                    LEFT JOIN Users u ON d.doctorID = u.userID";

                    $result = $db->query($sql);

                    if ($result) {
                        $data = array();
                        while ($row = $result->fetch_assoc()) {
                            // Fetch the doctor's schedule for each doctor with appointmentStatus = 'Scheduled'.
                            $scheduleQuery = "SELECT appointmentDate, appointmentTime
                                      FROM Appointments
                                      WHERE doctorID = " . $row['doctorID'] . " AND appointmentStatus = 'Scheduled'";

                            $scheduleResult = $db->query($scheduleQuery);

                            if ($scheduleResult) {
                                $scheduleData = array();
                                while ($scheduleRow = $scheduleResult->fetch_assoc()) {
                                    $scheduleData[] = $scheduleRow['appointmentDate'] . " " . $scheduleRow['appointmentTime'];
                                }
                                $row['schedule'] = $scheduleData;
                            } else {
                                $row['schedule'] = array();
                            }

                            // Only include doctor_name and schedule in the output, not all of the data.
                            $data[] = array(
                                'doctor_name' => $row['doctor_name'],
                                'schedule' => $row['schedule']
                            );
                        }

                        // Close the database connection.
                        $result->close();

                        $response = $data;
                        echo json_encode($response);
                    } else {
                        // Error handling.
                        $response["error"] = "Error retrieving doctor schedules: " . $db->error;
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
