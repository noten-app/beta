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
if (mysqli_connect_errno()) exit("Error with the Database");

// Check if school year exists for this user
if ($stmt = $con->prepare("SELECT COUNT(*) FROM " . $config["db"]["tables"]["years"] . " WHERE owner = ? AND id = ?")) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_POST["school_year"]);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        exit();
    }
}

// Set school year
$_SESSION["setting_year"] = $_POST["school_year"];
if ($stmt = $con->prepare("UPDATE " . $config["db"]["tables"]["accounts"] . " SET school_year = ? WHERE id = ?")) {
    $stmt->bind_param("ss", $_POST["school_year"], $_SESSION["user_id"]);
    $stmt->execute();

    // Redirect
    // header("Location: /settings");
    echo "UPDATE" . $config["db"]["tables"]["accounts"] . " SET school_year = " . $_SESSION["setting_year"] . " WHERE id = " . $_SESSION["user_id"];
}
