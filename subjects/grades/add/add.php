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
$grade = $_POST["grade"];
$gradeModifier = $_POST["gradeModifier"];
$date = $_POST["date"];
$note = $_POST["note"];
$type = $_POST["type"];
$subject_id = $_POST["subject"];

// Check if necessary input is given
if (!isset($grade)) die("missing-grade");
if (!isset($date)) die("missing-date");
if (!isset($type)) die("missing-type");
if (!isset($subject_id)) die("missing-subject");

// Check if subject is owned by user
if ($stmt = $con->prepare('SELECT user_id FROM ' . $config["db"]["tables"]["subjects"] . ' WHERE id = ?')) {
    $stmt->bind_param('i', $subject_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    if ($user_id !== $_SESSION["user_id"]) die("not-authorized");
    $stmt->close();
} else {
    die("error");
}

// Calculate grade to float
if ($gradeModifier === "full") $grade_float = $grade;
else {
    $grade_float = $grade;
    if (strval($gradeModifier == "025")) $grade_float += 0.25;
    if (strval($gradeModifier == "050")) $grade_float += 0.5;
    if (strval($gradeModifier == "075")) $grade_float += 0.75;
}

// Check if grade is valid
if ($grade_float < 0 || $grade_float > 6) die("invalid-grade");

// Check if date is valid
if (!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $date)) die("invalid-date");

// Check if type is valid (k,m,o,t)
if (!preg_match("/^[kmst]$/", $type)) die("invalid-type");

// Check if note is valid (max 25 chars)
if (strlen($note) > 25) die("invalid-note");

// Add grade
if ($stmt = $con->prepare('INSERT INTO ' . $config["db"]["tables"]["grades"] . ' (user_id, subject, note, type, date, grade, year) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
    $stmt->bind_param('sisssss', $_SESSION["user_id"], $subject_id, $note, $type, $date, $grade_float, $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();

    // Change subject last used
    if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["subjects"] . ' SET last_used = ? WHERE id = ?')) {
        $stmt->bind_param('si', $date, $subject_id);
        $stmt->execute();
        $stmt->close();
        exit("success");
    } else {
        die("error");
    }
} else {
    die("error");
}

// DB Con close
$con->close();
