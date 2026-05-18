<!-- <?php
// $host = "localhost";
// $user = "root";
// $pass = "";
// $db   = "remis_db";

// $conn = new mysqli($host, $user, $pass, $db);
// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?> -->

<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'remis_db';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die(json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Return connection for other files to use
?>