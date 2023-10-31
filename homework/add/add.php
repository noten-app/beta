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
$subject = $_POST["subject"];
$type = $_POST["type"];
$date_due = $_POST["date_due"];
$task = $_POST["task"];

// Check if necessary input is given
if (!isset($subject) || strlen($subject) == 0) die("missing-subject");
if (!isset($type)) die("missing-type");
if (!($type == "b" || $type == "v" || $type == "w" || $type == "o")) die("invalid-type");
if (!isset($date_due) || strlen($date_due) == 0) die("missing-date_due");
if (!isset($task) || strlen($task) == 0) die("missing-task");
if (strlen($task) > 75) die("too-long-task");

// Encode task
$task = htmlentities($task);

// Create given-date
$date_given = date("Y-m-d");

// Add subject to DB and get inserted ID
if ($stmt = $con->prepare('INSERT INTO ' . $config["db"]["tables"]["homework"] . ' (user_id, subject, given, deadline, text, type, year) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
    $stmt->bind_param('sisssss', $_SESSION["user_id"], $subject, $date_given, $date_due, $task, $type, $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
