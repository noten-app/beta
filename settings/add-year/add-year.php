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

// Generate new year id (8 chars)
$chars = "0123456789abcdefghikjlmnopqrstuvwxyz";
$year_id = "";
for ($i = 0; $i < 8; $i++) {
    $year_id .= $chars[rand(0, strlen($chars) - 1)];
}

// Get transfer classes
if (isset($_POST["transfer_classes"])) {
    $transfer_classes = $_POST["transfer_classes"];
    $sql_string = "SELECT * FROM classes WHERE year = ? AND user_id = ?";
    $sql_types = "ss";
    if (count($transfer_classes) > 0) {
        $sql_string .= " AND id IN (";
        foreach ($transfer_classes as $class) {
            $sql_string .= "?, ";
            $sql_types .= "s";
        }
        $sql_string = substr($sql_string, 0, -2);
        $sql_string .= ")";
    }
    if ($stmt = $con->prepare($sql_string)) {
        $stmt->bind_param($sql_types, $_SESSION["setting_years"], $_SESSION["user_id"], ...$transfer_classes);
        $stmt->execute();
        $result = $stmt->get_result();
        $classes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    // Insert new classes
    foreach ($classes as $class) {
        $regenerate = true;
        $class_id = "";
        while ($regenerate) {
            for ($i = 0; $i < 8; $i++) {
                $class_id .= $chars[rand(0, strlen($chars) - 1)];
            }
            if ($stmt = $con->prepare("SELECT id FROM " . $config["db"]["tables"]["classes"] . " WHERE id = ?")) {
                $stmt->bind_param("s", $class_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows == 0) {
                    $stmt->close();
                    $regenerate = false;
                    if ($stmt = $con->prepare("INSERT INTO " . $config["db"]["tables"]["classes"] . " (id, name, color, user_id, grade_k, grade_m, grade_t, grade_s, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                        $stmt->bind_param("sssssssss", $class_id, $class["name"], $class["color"], $_SESSION["user_id"], $class["grade_k"], $class["grade_m"], $class["grade_t"], $class["grade_s"], $year_id);
                        $stmt->execute();
                        $stmt->close();
                    }
                } else {
                    $stmt->close();
                }
            }
        }
    }
}

if ($stmt = $con->prepare("INSERT INTO " . config_table_name_school_years . " (id, name, owner) VALUES (?, ?, ?)")) {
    $stmt->bind_param("sss", $year_id, $_POST["year_name"], $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
}

// Set as current year
$_SESSION["setting_years"] = $year_id;

exit("success");
