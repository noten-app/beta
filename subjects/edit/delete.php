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
$subject_id = $_POST["id"];
if ($subject_id === "") die("no id given");

// Delete subject if belonging to user, then check if one was deleted
if ($stmt = $con->prepare('DELETE FROM subjects WHERE id = ? AND user_id = ?')) {
	$stmt->bind_param('ss', $subject_id, $_SESSION["user_id"]);
	$stmt->execute();
	if ($stmt->affected_rows === 0) die("no subject deleted");
	else if ($stmt->affected_rows === 1) {
		if ($stmt->prepare('DELETE FROM homework WHERE subject = ? AND user_id = ?')) {
			$stmt->bind_param('ss', $subject_id, $_SESSION["user_id"]);
			$stmt->execute();
			if ($stmt->prepare('DELETE FROM grades WHERE subject = ? AND user_id = ?')) {
				$stmt->bind_param('ss', $subject_id, $_SESSION["user_id"]);
				$stmt->execute();
				exit("success");
			} else die("Error with the Database");
		}
	} else die("too many subjects deleted");
} else {
	die("Error with the Database");
}
