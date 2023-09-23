<?php

// Check login state
require("../res/php/session.php");
start_session();
require("../res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require("../config.php");

// DB Connection
$con = mysqli_connect(
    config_db_host,
    config_db_user,
    config_db_password,
    config_db_name
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Check if school year exists for this user
if ($stmt = $con->prepare("SELECT COUNT(*) FROM " . config_table_name_school_years . " WHERE owner = ? AND id = ?")) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_POST["school_year"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        exit();
    }
}

// Set school year
$_SESSION["setting_years"] = $_POST["school_year"];
if ($stmt = $con->prepare("UPDATE " . config_table_name_accounts . " SET school_year = ? WHERE id = ?")) {
    $stmt->bind_param("ss", $_SESSION["school_year"], $_SESSION["user_id"]);
    $stmt->execute();
}

// Redirect
header("Location: /settings");
