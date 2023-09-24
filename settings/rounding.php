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
$rounding = $_POST["rounding"];

// If rounding is not 0, 1 or 2, set it to 0
if ($rounding !== "0" && $rounding !== "1" && $rounding !== "2") $rounding = "0";

// Update rounding in DB
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["accounts"] . ' SET rounding = ? WHERE id = ?')) {
    $stmt->bind_param('ss', $rounding, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
} else die("Error with the Database");

// Set session variable
$_SESSION["setting_rounding"] = $rounding;

// DB Con close
$con->close();
