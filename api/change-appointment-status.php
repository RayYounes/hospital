<?php

include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointmentID']) && isset($_POST['appointmentStatus'])) {
    //Getting the data from the POST method.
    $appointmentID = $_POST['appointmentID'];
    $appointmentStatus = $_POST['appointmentStatus'];

    $query = "UPDATE Appointments SET appointmentStatus = ? WHERE appointmentID = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('si', $appointmentStatus, $appointmentID);

    if ($stmt->execute()) {
        echo "Appointment status updated successfully.";
    } else {
        echo "Error updating appointment status: " . $stmt->error;
    }
} else {
    echo "Invalid request. Please provide appointmentID and appointmentStatus.";
}
