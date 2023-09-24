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
$checked = $_POST["checked"];
$entry_id = $_POST["entry_id"];

// Check if necessary input is given
if (!isset($checked) || strlen($checked) == 0) die("missing-check-state");
if (!isset($entry_id)) die("missing-entry-id");

// Update entry
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["homework"] . ' SET status = ? WHERE entry_id = ? AND user_id = ?')) {
    $stmt->bind_param("iis", $checked, $entry_id, $_SESSION["user_id"]);
    $stmt->execute();
    // Check if entry was updated
    if ($stmt->affected_rows == 0) die("no-entry-updated");
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
