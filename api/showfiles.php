<?php

header("Content-Type: application/json");

include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['doctorID'])) {
    $doctorID = $_POST['doctorID']; // Get the doctorID from the request.

    // Query to retrieve the patientID associated with the provided doctorID.
    $patientQuery = "SELECT patientID FROM DoctorPatientMap WHERE doctorID = ? AND status = 1";
    $patientStmt = $db->prepare($patientQuery);
    $patientStmt->bind_param('i', $doctorID);

    $response = array(); // Create an empty array for the response data.

    if ($patientStmt->execute()) {
        $patientResult = $patientStmt->get_result();

        if ($patientResult->num_rows > 0) {
            // Get the patientID associated with the doctorID.
            $patientRow = $patientResult->fetch_assoc();
            $patientID = $patientRow['patientID'];

            // Query to retrieve all rows from the Files table with the matching patientID.
            $filesQuery = "SELECT filePath FROM Files WHERE patientID = ?";
            $filesStmt = $db->prepare($filesQuery);
            $filesStmt->bind_param('i', $patientID);

            if ($filesStmt->execute()) {
                $filesResult = $filesStmt->get_result();

                if ($filesResult->num_rows > 0) {
                    $response["files"] = array(); // Create an array for files data.

                    while ($fileRow = $filesResult->fetch_assoc()) {
                        array_push($response["files"], $fileRow['filePath']);
                    }
                } else {
                    $response["error"] = "No files found for this patient.";
                }
            } else {
                $response["error"] = "Error retrieving files: " . $filesStmt->error;
            }
        } else {
            $response["error"] = "No patient assigned to the provided doctor";
        }
    } else {
        $response["error"] = "Error retrieving patient: " . $patientStmt->error;
    }

    // Return the JSON response.
    echo json_encode($response);
} else {
    // Handle the case where doctorID is not provided in the API request.
    echo json_encode(array("error" => "doctorID parameter is required."));
}

$db->close();
