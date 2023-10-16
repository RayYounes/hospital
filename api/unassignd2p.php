<?php

include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID']) && isset($_POST['doctorID'])) {
    // POST method data.
    $patientID = $_POST['patientID'];
    $doctorID = $_POST['doctorID'];

    // Check if the doctor-patient relationship exists in the DoctorPatientMap table.
    $query = "SELECT * FROM DoctorPatientMap WHERE doctorID = ? AND patientID = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('ii', $doctorID, $patientID);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            // The relationship doesn't exist, so the doctor is not assigned to the patient.
            echo "Error: Doctor is not assigned to this patient.";
        } else {
            // Update the status to 0 to unassign the doctor from the patient.
            // In the unassign process, I don't want to delete the row so I just change the status.
            $updateQuery = "UPDATE DoctorPatientMap SET status = 0 WHERE doctorID = ? AND patientID = ?";
            $updateStmt = $db->prepare($updateQuery);
            $updateStmt->bind_param('ii', $doctorID, $patientID);

            if ($updateStmt->execute()) {
                // Successfully unassigned the doctor from the patient by updating the status to 0.
                echo "Doctor unassigned from patient";
            } else {
                // Error handling for the update operation.
                echo "Error unassigning doctor from patient: " . $updateStmt->error;
            }
        }
    } else {
        // Error handling for executing the query.
        echo "Error checking the doctor-patient relationship: " . $stmt->error;
    }
}
