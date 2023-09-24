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
$className = $_POST["className"];
$testCustom = $_POST["testCustom"];
$gradingTypeK = $_POST["gradingTypeK"];
$gradingTypeM = $_POST["gradingTypeM"];
$gradingTypeT = $_POST["gradingTypeT"];
$gradingTypeS = $_POST["gradingTypeS"];
$classColor = $_POST["classColor"];

// Check if necessary input is given
if (!isset($className) || strlen($className) == 0) die("missing-classname");
if (!isset($testCustom)) die("missing-testcustom");
if (!isset($gradingTypeK)) die("missing-gradingtypeK");
if (!isset($gradingTypeM)) die("missing-gradingtypeM");
if ($testCustom == "true" && !isset($gradingTypeT)) die("missing-gradingtypeT");
if (!isset($gradingTypeS)) die("missing-gradingtypeS");
if (!isset($classColor)) die("missing-classcolor");

// Generate gradeTypeT if not custom
if ($testCustom == "false") $gradingTypeT = "1exam";
else $gradingTypeT = strval($gradingTypeT);

// Remove # from color
$classColor = str_replace("#", "", $classColor);

// Add class to DB and get inserted ID
if ($stmt = $con->prepare('INSERT INTO ' . $config["db"]["tables"]["classes"] . ' (name, color, user_id, grade_k, grade_m, grade_t, grade_s, year) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')) {
    $stmt->bind_param('sssiisis', $className, $classColor, $_SESSION["user_id"], $gradingTypeK, $gradingTypeM, $gradingTypeT, $gradingTypeS, $_SESSION["setting_years"]);
    $stmt->execute();
    $classID = $stmt->insert_id;
    $stmt->close();
    exit(json_encode(array("success" => true, "classID" => $classID)));
} else die("Error with the Database");

// DB Con close
$con->close();
