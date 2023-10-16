<?php
include('dbcon.php');
require 'vendor/autoload.php'; // Include the JWT library.

use Firebase\JWT\JWT;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // POST method data.
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Secret key and algorithm.
    $key = 'ray123';
    $algorithm = 'HS256';

    // Validate username and hashed password in your database.
    $query = "SELECT * FROM Users WHERE username = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $storedPasswordHash = $row['passwordHash'];

        //Decrypte the password.
        if (password_verify($password, $storedPasswordHash)) {
            // User is authenticated, generate a JWT token.
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; // JWT will expire in 1 hour.
            $data = array(
                'username' => $username, // Include username in the token.
                'user_id' => $row['userID'], // Include user_id in the token.
                'userType' => $row['userType'], // Include userType in the token.
            );

            $token = JWT::encode($data, $key, 'HS256');

            // Set the HTTP response code and add the token to the "Authorization" header.
            http_response_code(200); // Response 200.
            header("Authorization: Bearer " . $token);

            echo json_encode(array(
                'message' => 'Authentication successful',
                $token
            ));
        } else {
            // Password doesn't match.
            http_response_code(401); // Unauthorized response 401
            echo json_encode(array("message" => "Authentication failed."));
        }
    } else {
        // User not found.
        http_response_code(401); // Unauthorized
        echo json_encode(array("message" => "User not found."));
    }
}
