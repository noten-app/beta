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

// Get transfer subjects
if (isset($_POST["transfer_subjects"])) {
    $transfer_subjects = $_POST["transfer_subjects"];
    $sql_string = "SELECT * FROM subjects WHERE year = ? AND user_id = ?";
    $sql_types = "ss";
    if (count($transfer_subjects) > 0) {
        $sql_string .= " AND id IN (";
        foreach ($transfer_subjects as $subject) {
            $sql_string .= "?, ";
            $sql_types .= "s";
        }
        $sql_string = substr($sql_string, 0, -2);
        $sql_string .= ")";
    }
    if ($stmt = $con->prepare($sql_string)) {
        $stmt->bind_param($sql_types, $_SESSION["setting_years"], $_SESSION["user_id"], ...$transfer_subjects);
        $stmt->execute();
        $result = $stmt->get_result();
        $subjects = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
    // Insert new subjects
    foreach ($subjects as $subject) {
        $regenerate = true;
        $subject_id = "";
        while ($regenerate) {
            for ($i = 0; $i < 8; $i++) {
                $subject_id .= $chars[rand(0, strlen($chars) - 1)];
            }
            if ($stmt = $con->prepare("SELECT id FROM " . $config["db"]["tables"]["subjects"] . " WHERE id = ?")) {
                $stmt->bind_param("s", $subject_id);
                $stmt->execute();
                if ($stmt->get_result()->num_rows == 0) {
                    $stmt->close();
                    $regenerate = false;
                    if ($stmt = $con->prepare("INSERT INTO " . $config["db"]["tables"]["subjects"] . " (id, name, color, user_id, weight_exam, weight_oral, weight_test, weight_other, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                        $stmt->bind_param("sssssssss", $subject_id, $subject["name"], $subject["color"], $_SESSION["user_id"], $subject["weight_exam"], $subject["weight_oral"], $subject["weight_test"], $subject["weight_other"], $year_id);
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

if ($stmt = $con->prepare("INSERT INTO " . $config["db"]["tables"]["years"] . " (id, name, owner) VALUES (?, ?, ?)")) {
    $stmt->bind_param("sss", $year_id, $_POST["year_name"], $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->close();
}

// Set as current year
$_SESSION["setting_years"] = $year_id;

exit("success");
