<?php

// Check login state
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/session.php");
start_session();
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require($_SERVER["DOCUMENT_ROOT"] . "/config.php");

// DB Connection
$con = mysqli_connect(
    $config["db"]["credentials"]["host"],
    $config["db"]["credentials"]["user"],
    $config["db"]["credentials"]["password"],
    $config["db"]["credentials"]["name"]
);
if (mysqli_connect_errno()) die("Error with the Database");

// Get input
$grade_id = $_POST["grade_id"];

// Check if grade is owned by user
if ($stmt = $con->prepare('SELECT user_id FROM ' . $config["db"]["tables"]["grades"] . ' WHERE id = ?')) {
    $stmt->bind_param('i', $grade_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    if ($user_id !== $_SESSION["user_id"]) die("not-authorized");
    $stmt->close();
} else {
    die("error");
}

// Delete grade
if ($stmt = $con->prepare('DELETE FROM ' . $config["db"]["tables"]["grades"] . ' WHERE id = ?')) {
    $stmt->bind_param('s', $grade_id);
    $stmt->execute();
    $stmt->close();

    // Change class last used
    if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["classes"] . ' SET last_used = ? WHERE id = ?')) {
        $stmt->bind_param('si', $date, $class_id);
        $stmt->execute();
        $stmt->close();
        exit("success");
    } else {
        die("error");
    }
} else {
    die("error");
}

// DB Con close
$con->close();
