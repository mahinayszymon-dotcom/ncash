<?php


$servername = "127.0.0.1:3308";
$username = "developer_ncash"; 
$password = "devpass123";     
$database = "ncash_tracemo"; 

// ncash_pawnshop_1100

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

// echo "<p style=\"font-family: Arial; font-size: 60px;\">Database connected successfully!</p>";

?>
