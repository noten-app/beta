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
$class = $_POST["class"];
$type = $_POST["type"];
$date_due = $_POST["date_due"];
$task = $_POST["task"];
$task_id = $_POST["task_id"];

// Check if necessary input is given
if (!isset($class) || strlen($class) == 0) die("missing-class");
if (!isset($type)) die("missing-type");
if (!($type == "b" || $type == "v" || $type == "w" || $type == "o")) die("invalid-type");
if (!isset($date_due) || strlen($date_due) == 0) die("missing-date_due");
if (!isset($task) || strlen($task) == 0) die("missing-task");
if (strlen($task) > 75) die("too-long-task");

// Encode task
$task = htmlentities($task);

// Create given-date
$date_given = date("Y-m-d");

// Update task in DB
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["homework"] . ' SET class = ?, given = ?, deadline = ?, text = ?, type = ? WHERE entry_id = ? AND user_id = ?')) {
    $stmt->bind_param('sssssis', $class, $date_given, $date_due, $task, $type, $task_id, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
