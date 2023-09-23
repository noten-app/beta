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
    config_db_host,
    config_db_user,
    config_db_password,
    config_db_name
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Delete all grades
if ($stmt = $con->prepare("DELETE FROM " . config_table_name_grades . " WHERE user_id = ? AND year = ?")) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();
}

// Delete all classes
if ($stmt = $con->prepare("DELETE FROM " . config_table_name_classes . " WHERE user_id = ? AND year = ?")) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();
}

// Delete homework
if ($stmt = $con->prepare("DELETE FROM " . config_table_name_homework . " WHERE user_id = ? AND year = ?")) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();
}

// Delete year
if ($stmt = $con->prepare("DELETE FROM " . config_table_name_school_years . " WHERE id = ? AND owner = ?")) {
    $stmt->bind_param("ss", $_SESSION["setting_years"], $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
}

$con->close();

exit("success");
