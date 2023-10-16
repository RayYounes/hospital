<?php

include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['patientID']) && isset($_POST['doctorID']) && isset($_POST['appointmentDate']) && isset($_POST['appointmentTime'])) {
    // POST method data.
    $patientID = $_POST['patientID'];
    $doctorID = $_POST['doctorID'];
    $appointmentDate = $_POST['appointmentDate'];
    $appointmentTime = $_POST['appointmentTime'];

    // Check if both patientID and doctorID with status = 1 exist in the doctorpatientmap table.
    $checkQuery = "SELECT COUNT(*) as count FROM doctorpatientmap WHERE patientID = ? AND doctorID = ? AND status = 1";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bind_param('ii', $patientID, $doctorID);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $row = $checkResult->fetch_assoc();
    $count = $row['count'];

    if ($count > 0) {
        // Both patientID and doctorID with status = 1 are found in the doctorpatientmap table.

        // Fetch existing appointments for the same appointmentDate, doctorID, and patientID.
        $fetchQuery = "SELECT appointmentTime FROM Appointments WHERE doctorID = ? AND patientID = ? AND appointmentDate = ?";
        $fetchStmt = $db->prepare($fetchQuery);
        $fetchStmt->bind_param('iis', $doctorID, $patientID, $appointmentDate);
        $fetchStmt->execute();
        $existingAppointments = $fetchStmt->get_result();

        $isAppointmentValid = true;

        while ($row = $existingAppointments->fetch_assoc()) {
            $existingTime = strtotime($row['appointmentTime']);
            $newTime = strtotime($appointmentTime);

            // Check if the new appointment time is within 1 hour (3600 seconds) of any existing appointment.
            if (abs($newTime - $existingTime) < 3600) {
                $isAppointmentValid = false;
                break;
            }
        }

        if ($isAppointmentValid) {
            // Insert a new appointment into the Appointments table.
            $insertQuery = "INSERT INTO Appointments (patientID, doctorID, appointmentDate, appointmentTime, appointmentStatus) VALUES (?, ?, ?, ?, 'Scheduled')";
            $insertStmt = $db->prepare($insertQuery);
            $insertStmt->bind_param('iiss', $patientID, $doctorID, $appointmentDate, $appointmentTime);

            if ($insertStmt->execute()) {
                // Successfully scheduled the appointment.
                echo "Appointment scheduled successfully.";
            } else {
                // Error handling.
                echo "Error scheduling appointment: " . $insertStmt->error;
            }
        } else {
            // Invalid appointment time.
            echo "Appointment time should have a 1-hour difference from existing appointments.";
        }
    } else {
        // Patient and doctor not assigned with status = 1 or not found.
        echo "Patient and doctor are not assigned for scheduling an appointment.";
    }
}
