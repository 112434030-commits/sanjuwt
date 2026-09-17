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
   ACTION
========================= */

$action = $_POST["action"] ?? "";


/* =========================
   REGISTER
========================= */

if ($action === "register") {


    $name =
        trim($_POST["name"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $phone =
        trim($_POST["phone"] ?? "");

    $password =
        $_POST["password"] ?? "";


    /* NAME */

    if (!preg_match(
        "/^[A-Za-z ]{2,100}$/",
        $name
    )) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Name can contain only letters and spaces."
        ]);

        exit;
    }


    /* EMAIL */

    if (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Please enter a valid email."
        ]);

        exit;
    }


    /* PHONE */

    if (!preg_match(
        "/^[6-9][0-9]{9}$/",
        $phone
    )) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Enter a valid 10-digit phone number."
        ]);

        exit;
    }


    /* PASSWORD */

    if (strlen($password) < 6) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Password must contain at least 6 characters."
        ]);

        exit;
    }


    /* CHECK EMAIL */

    $check = $conn->prepare(
        "SELECT id
         FROM users
         WHERE email = ?"
    );

    $check->bind_param(
        "s",
        $email
    );

    $check->execute();

    $result =
        $check->get_result();


    if ($result->num_rows > 0) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Email is already registered."
        ]);

        exit;
    }


    /* HASH PASSWORD */

    $hashedPassword =
        password_hash(
            $password,
            PASSWORD_DEFAULT
        );


    /* INSERT */

    $stmt = $conn->prepare(
        "INSERT INTO users
        (name, email, phone, password)
        VALUES (?, ?, ?, ?)"
    );


    $stmt->bind_param(
        "ssss",
        $name,
        $email,
        $phone,
        $hashedPassword
    );


    if ($stmt->execute()) {

        echo json_encode([
            "success" => true,
            "message" =>
                "Registration successful. Please login."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" =>
                "Registration failed."
        ]);
    }


    exit;
}


/* =========================
   LOGIN
========================= */

if ($action === "login") {


    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Invalid email address."
        ]);

        exit;
    }


    if (strlen($password) < 6) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Invalid password."
        ]);

        exit;
    }


    $stmt = $conn->prepare(
        "SELECT id, name, password
         FROM users
         WHERE email = ?"
    );


    $stmt->bind_param(
        "s",
        $email
    );

    $stmt->execute();


    $result =
        $stmt->get_result();


    if ($result->num_rows === 0) {

        echo json_encode([
            "success" => false,
            "message" =>
                "Invalid email or password."
        ]);

        exit;
    }


    $user =
        $result->fetch_assoc();


    if (
        password_verify(
            $password,
            $user["password"]
        )
    ) {


        /* CREATE SESSION */

        $_SESSION["user_id"] =
            $user["id"];

        $_SESSION["user_name"] =
            $user["name"];


        echo json_encode([
            "success" => true,
            "message" =>
                "Login successful."
        ]);

    } else {

        echo json_encode([
            "success" => false,
            "message" =>
                "Invalid email or password."
        ]);
    }


    exit;
}


/* INVALID REQUEST */

echo json_encode([
    "success" => false,
    "message" => "Invalid request."
]);


$conn->close();

?>