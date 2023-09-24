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
$system = $_POST["system"];

// If system is not "noten" or "punkte" set it to "noten"
if ($system != "noten" && $system != "punkte") $system = "noten";

// Update system in DB
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["accounts"] . ' SET gradesystem = ? WHERE id = ?')) {
    $stmt->bind_param('ss', $system, $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
} else die("Error with the Database");

// Set session variable
$_SESSION["setting_system"] = $system;

// Check if user has grades their floats end in .5
if ($system == "punkte") {
    if ($stmt = $con->prepare('SELECT grade FROM ' . $config["db"]["tables"]["grades"] . ' WHERE user_id = ?')) {
        $stmt->bind_param('s', $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else die("Error with the Database");
    while ($row = $result->fetch_assoc()) {
        if (strpos($row["grade"], ".5") !== false) {
            // Exit -> User has grades their floats end in .5
            exit("warn");
        }
    }
}

// DB Con close
$con->close();

// Exit -> No problems
exit("success");
