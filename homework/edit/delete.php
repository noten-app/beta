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

// Get taskID
$task_id = $_POST["task_id"];

// Update task in DB
if ($stmt = $con->prepare('DELETE FROM ' . $config["db"]["tables"]["homework"] . ' WHERE entry_id = ? AND user_id = ?')) {
    $stmt->bind_param('is', $task_id, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
