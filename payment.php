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

$paymentMethod =
    trim($_POST["payment_method"] ?? "");

$amount =
    floatval($_POST["amount"] ?? 0);


/* VALID PAYMENT METHODS */

$allowedMethods = [
    "Cash",
    "UPI",
    "Card"
];


if (!in_array(
    $paymentMethod,
    $allowedMethods,
    true
)) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid payment method."
    ]);

    exit;
}


/* CHECK BOOKING */

$stmt = $conn->prepare(
    "SELECT fare
     FROM bookings
     WHERE id = ?
     AND user_id = ?
     AND status = 'Vehicle Selected'"
);


$stmt->bind_param(
    "ii",
    $bookingId,
    $userId
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Booking not found or not ready for payment."
    ]);

    exit;
}


$booking =
    $result->fetch_assoc();


/* USE DATABASE FARE */

$correctAmount =
    floatval($booking["fare"]);


if ($correctAmount <= 0) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Invalid booking fare."
    ]);

    exit;
}


/* =========================
   INSERT PAYMENT
========================= */

$stmt = $conn->prepare(
    "INSERT INTO payments
    (
        booking_id,
        payment_method,
        amount,
        payment_status
    )
    VALUES (?, ?, ?, 'Paid')"
);


$stmt->bind_param(
    "isd",
    $bookingId,
    $paymentMethod,
    $correctAmount
);


if (!$stmt->execute()) {

    echo json_encode([
        "success" => false,
        "message" =>
            "Payment failed."
    ]);

    exit;
}


$paymentId =
    $stmt->insert_id;


/* UPDATE BOOKING */

$update = $conn->prepare(
    "UPDATE bookings

     SET status = 'Paid'

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
        "Payment successful.",
    "payment_id" =>
        $paymentId
]);


$conn->close();

?>