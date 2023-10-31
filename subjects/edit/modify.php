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
$subjectName = $_POST["subjectName"];
$testCustom = $_POST["testCustom"];
$gradingTypeK = $_POST["gradingTypeK"];
$gradingTypeM = $_POST["gradingTypeM"];
$gradingTypeT = $_POST["gradingTypeT"];
$gradingTypeS = $_POST["gradingTypeS"];
$subjectColor = $_POST["subjectColor"];
$subjectID = $_POST["subjectID"];

// Check if necessary input is given
if (!isset($subjectName) || strlen($subjectName) == 0) die("missing-subjectname");
if (!isset($testCustom)) die("missing-testcustom");
if (!isset($gradingTypeK)) die("missing-gradingtypeK");
if (!isset($gradingTypeM)) die("missing-gradingtypeM");
if ($testCustom == "true" && !isset($gradingTypeT)) die("missing-gradingtypeT");
if (!isset($gradingTypeS)) die("missing-gradingtypeS");
if (!isset($subjectColor)) die("missing-subjectcolor");
if (!isset($subjectID)) die("missing-subjectid");

// Check if subject exists and belongs to user
if ($stmt = $con->prepare('SELECT user_id FROM ' . $config["db"]["tables"]["subjects"] . ' WHERE id = ?')) {
    $stmt->bind_param('i', $subjectID);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($userID);
    $stmt->fetch();
    if ($userID !== $_SESSION["user_id"]) die("missing-subject");
    $stmt->close();
} else die("Error with the Database");

// Generate gradeTypeT if not custom
if ($testCustom == "false") $gradingTypeT = "1exam";
else $gradingTypeT = strval($gradingTypeT);

// Remove # from color
$subjectColor = str_replace("#", "", $subjectColor);

// Make an sql statement to update the subject
// exit("UPDATE ".$config["db"]["tables"]["subjects"]." SET name = '".$subjectName."', color = '".$subjectColor."', grade_k = ".$gradingTypeK.", grade_m = ".$gradingTypeM.", grade_t = '".$gradingTypeT."', grade_s = ".$gradingTypeS." WHERE id = ".$subjectID);

// Update subject in DB
if ($stmt = $con->prepare('UPDATE ' . $config["db"]["tables"]["subjects"] . ' SET name = ?, color = ?, grade_k = ?, grade_m = ?, grade_t = ?, grade_s = ? WHERE id = ?')) {
    $stmt->bind_param('ssiisii', $subjectName, $subjectColor, $gradingTypeK, $gradingTypeM, $gradingTypeT, $gradingTypeS, $subjectID);
    $stmt->execute();
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
