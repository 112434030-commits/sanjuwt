<?php

session_start();

header("Content-Type: application/json");


/* =========================
   DATABASE
========================= */

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
        "message" => "Database connection failed."
    ]);

    exit;
}


/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION["user_id"])) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Please login before booking a ride."
    ]);

    exit;
}


$userId =
    intval($_SESSION["user_id"]);


/* =========================
   INPUT
========================= */

$pickup =
    trim($_POST["pickup"] ?? "");

$destination =
    trim($_POST["destination"] ?? "");

$rideDate =
    $_POST["ride_date"] ?? "";

$rideTime =
    $_POST["ride_time"] ?? "";


/* =========================
   VALIDATION
========================= */

if (
    strlen($pickup) < 2 ||
    strlen($pickup) > 255
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid pickup location."
    ]);

    exit;
}


if (
    strlen($destination) < 2 ||
    strlen($destination) > 255
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid destination."
    ]);

    exit;
}


if (
    strtolower($pickup) ===
    strtolower($destination)
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Pickup and destination cannot be the same."
    ]);

    exit;
}


/* DATE */

$dateObject =
    DateTime::createFromFormat(
        "Y-m-d",
        $rideDate
    );


if (
    !$dateObject ||
    $dateObject->format("Y-m-d") !== $rideDate
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid ride date."
    ]);

    exit;
}


/* TIME */

$timeObject =
    DateTime::createFromFormat(
        "H:i",
        $rideTime
    );


if (
    !$timeObject ||
    $timeObject->format("H:i") !== $rideTime
) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid ride time."
    ]);

    exit;
}


/* FUTURE CHECK */

$rideDateTime =
    new DateTime(
        $rideDate . " " . $rideTime
    );

$currentDateTime =
    new DateTime();


if ($rideDateTime <= $currentDateTime) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Ride date and time must be in the future."
    ]);

    exit;
}


/* =========================
   INSERT BOOKING
========================= */

$stmt = $conn->prepare(
    "INSERT INTO bookings
    (
        user_id,
        pickup,
        destination,
        ride_date,
        ride_time,
        status
    )
    VALUES (?, ?, ?, ?, ?, 'Pending')"
);


$stmt->bind_param(
    "issss",
    $userId,
    $pickup,
    $destination,
    $rideDate,
    $rideTime
);


if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" =>
            "Booking created successfully.",
        "booking_id" =>
            $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" =>
            "Unable to create booking."
    ]);
}


$conn->close();

?>