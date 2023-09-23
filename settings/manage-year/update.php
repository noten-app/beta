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

// Validate input
if (!isset($_POST["year_name"])) {
    echo "Error: Missing input";
    exit();
}
if (strlen($_POST["year_name"]) > 20) {
    echo "Error: Year name too long";
    exit();
}
if (strlen($_POST["year_name"]) < 1) {
    echo "Error: Year name too short";
    exit();
}

// Update year
if ($stmt = $con->prepare("UPDATE " . config_table_name_school_years . " SET name = ? WHERE id = ? AND owner = ?")) {
    $stmt->bind_param("sss", $_POST["year_name"], $_SESSION["setting_years"], $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
}

// Set new year
$_SESSION["setting_years"] = isset($_POST["next"]) ? $_POST["next"] : "";

exit("success");
