<?php
$name = $_GET['name'];
$email = $_GET['email'];
$password = $_GET['password'];

echo "<h2>Registration Details</h2>";
echo "Name: " . htmlspecialchars($name) . "<br>";
echo "Email: " . htmlspecialchars($email) . "<br>";
echo "Password: " . htmlspecialchars($password) . "<br>";
?>