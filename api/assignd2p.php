<?php

include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID']) && isset($_POST['doctorID'])) {
    //Getting the data from the POST method.
    $patientID = $_POST['patientID'];
    $doctorID = $_POST['doctorID'];

    // Check if the doctorID exists in the Doctors table.
    $query2 = "SELECT doctorID FROM Doctors WHERE doctorID = ?";
    $stmt2 = $db->prepare($query2);
    $stmt2->bind_param('i', $doctorID);

    // Check if the patientID exists in the Patients table.
    $query3 = "SELECT patientID FROM Patients WHERE patientID = ?";
    $stmt3 = $db->prepare($query3);
    $stmt3->bind_param('i', $patientID);

    if ($stmt2 && $stmt3) {
        if ($stmt2->execute()) {
            $stmt2->store_result();

            if ($stmt2->num_rows === 0) {
                // The doctorID does not exist in the Doctors table.
                echo "Error: Doctor not found.";
            } else {
                if ($stmt3->execute()) {
                    $stmt3->store_result();

                    if ($stmt3->num_rows === 0) {
                        // The patientID does not exist in the Patients table.
                        echo "Error: Patient not found.";
                    } else {
                        // If both of them are found insert into the table of DoctorPatientMap.
                        $sql = "INSERT INTO DoctorPatientMap (doctorID, patientID, status) VALUES (?, ?,'1')";
                        $stmt = $db->prepare($sql);
                        $stmt->bind_param('ii', $doctorID, $patientID);

                        if ($stmt->execute()) {
                            // Successfully assigned doctor to the patient.
                            echo "Doctor assigned to patient.";
                        } else {
                            // Error handling.
                            echo "Error assigning doctor to patient: " . $stmt->error;
                        }
                    }
                } else {
                    // Error handling for executing the patient query.
                    echo "Error checking patient: " . $stmt3->error;
                }
            }
        } else {
            // Error handling for executing the doctor query.
            echo "Error checking doctor: " . $stmt2->error;
        }
    } else {
        // Error handling for preparing the doctor and patient queries.
        echo "Error preparing doctor or patient queries: " . $db->error;
    }
}
