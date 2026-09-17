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

$vehicle =
    trim($_POST["vehicle"] ?? "");

$fare =
    floatval($_POST["fare"] ?? 0);


/* VALID VEHICLES */

$vehicleFares = [

    "Bike" => 50,

    "Auto" => 90,

    "Car" => 150

];


/* VALIDATE VEHICLE */

if (!array_key_exists(
    $vehicle,
    $vehicleFares
)) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid vehicle."
    ]);

    exit;
}


/* SERVER-SIDE FARE */

$correctFare =
    $vehicleFares[$vehicle];


/* DO NOT TRUST CLIENT FARE */

if ($fare != $correctFare) {

    $fare =
        $correctFare;
}


/* BOOKING ID */

if ($bookingId <= 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid booking."
    ]);

    exit;
}


/* CHECK BOOKING BELONGS TO USER */

$check = $conn->prepare(
    "SELECT id
     FROM bookings
     WHERE id = ?
     AND user_id = ?"
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


/* UPDATE */

$stmt = $conn->prepare(
    "UPDATE bookings

     SET vehicle = ?,
         fare = ?,
         status = 'Vehicle Selected'

     WHERE id = ?
     AND user_id = ?"
);


$stmt->bind_param(
    "sdii",
    $vehicle,
    $correctFare,
    $bookingId,
    $userId
);


if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" =>
            "Vehicle selected successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" =>
            "Vehicle selection failed."
    ]);
}


$conn->close();

?>