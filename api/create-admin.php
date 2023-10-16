<?php
include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['name']) && isset($_POST['email'])) {
    //Retrieve data from POST method.
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $email = $_POST['email'];

    // Hash the password.
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insert a new ADMIN into the Users table.
    // Not restricted so the tester can create atleast 1 admin for test.
    $sql = "INSERT INTO Users (username, passwordHash, userType, name, email) VALUES (?, ?, 'Admin', ?, ?)";
    $stmt = $db->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('ssss', $username, $passwordHash, $name, $email);

        if ($stmt->execute()) {
            // Successfully created the user.
            echo "User created successfully.";
        } else {
            // Error handling for the statement execution.
            echo "Error creating user: " . $stmt->error;
        }
    } else {
        // Error handling for preparing the statement.
        echo "Error creating user: " . $db->error;
    }
}
