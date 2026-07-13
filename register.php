<!DOCTYPE html>
<html>
<head>
    <title>Registration Details</title>
</head>
<body>

<h2>Submitted Details</h2>

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $age = $_POST['age'];
    $course = $_POST['course'];

    if (isset($_POST['hobbies'])) {
        $hobbies = implode(", ", $_POST['hobbies']);
    } else {
        $hobbies = "None";
    }

    echo "<b>Full Name:</b> $fullname <br>";
    echo "<b>Email:</b> $email <br>";
    echo "<b>Password:</b> $password <br>";
    echo "<b>Gender:</b> $gender <br>";
    echo "<b>Age:</b> $age <br>";
    echo "<b>Course:</b> $course <br>";
    echo "<b>Hobbies:</b> $hobbies <br>";
}
else {
    echo "No data received.";
}

?>

</body>
</html>