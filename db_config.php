<?php
// db_config.php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'sports_management';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
