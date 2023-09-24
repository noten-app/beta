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
$sorting = $_POST["sorting"];

// If sorting is not average, alphabet or lastuse
if ($sorting !== "average" && $sorting !== "alphabet" && $sorting !== "lastuse") $sorting = "average";

// Update sorting in DB
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["accounts"] . ' SET sorting = ? WHERE id = ?')) {
    $stmt->bind_param('ss', $sorting, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
} else die("Error with the Database");

// Set session variable
$_SESSION["setting_sorting"] = $sorting;

// DB Con close
$con->close();
