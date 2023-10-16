<?php

//create connection to the database
$servername = "localhost";
$username = "root";
$password = "";
$database = "hospital";


$db = new mysqli($servername, $username, $password, $database);

// Check if connection works 
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
