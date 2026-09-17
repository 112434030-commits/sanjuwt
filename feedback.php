<?php

session_start();

header("Content-Type: application/json");


/* DATABASE */

$host = "localhost";
$username = "root";
$password = "";
$database = "rideon";


$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);


if ($conn->connect_error) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Database connection failed."
    ]);

    exit;
}


/* LOGIN */

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Please login first."
    ]);

    exit;
}


$userId =
    intval($_SESSION["user_id"]);


/* INPUT */

$bookingId =
    intval($_POST["booking_id"] ?? 0);

$rating =
    intval($_POST["rating"] ?? 0);

$comment =
    trim($_POST["comment"] ?? "");


/* RATING */

if (
    $rating < 1 ||
    $rating > 5
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Rating must be between 1 and 5."
    ]);

    exit;
}


/* COMMENT LENGTH */

if (strlen($comment) > 500) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Feedback cannot exceed 500 characters."
    ]);

    exit;
}


/* BOOKING CHECK */

$check = $conn->prepare(
    "SELECT id
     FROM bookings
     WHERE id = ?
     AND user_id = ?
     AND status = 'Paid'"
);


$check->bind_param(
    "ii",
    $bookingId,
    $userId
);


$check->execute();


$result =
    $check->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Booking not found."
    ]);

    exit;
}


/* INSERT FEEDBACK */

$stmt = $conn->prepare(
    "INSERT INTO feedback
    (
        booking_id,
        rating,
        comment
    )
    VALUES (?, ?, ?)"
);


$stmt->bind_param(
    "iis",
    $bookingId,
    $rating,
    $comment
);


if (!$stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Unable to submit feedback."
    ]);

    exit;
}


/* COMPLETE BOOKING */

$update = $conn->prepare(
    "UPDATE bookings

     SET status = 'Completed'

     WHERE id = ?
     AND user_id = ?"
);


$update->bind_param(
    "ii",
    $bookingId,
    $userId
);


$update->execute();


echo json_encode([
    "success" => true,
    "message" =>
        "Thank you! Your feedback was submitted."
]);


$conn->close();

?>