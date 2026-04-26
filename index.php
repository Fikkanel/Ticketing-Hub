<?php
echo "<h1>Hello from Docker!</h1>";
echo "<p>Nginx & PHP are working perfectly.</p>";

$host = 'mysql';
$user = 'testuser';
$pass = 'testpassword';
$db = 'testdb';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "<p style='color: red;'>MySQL connection failed: " . $conn->connect_error . "</p>";
} else {
    echo "<p style='color: green;'>MySQL connection successful!</p>";
}
?>
