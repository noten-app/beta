<?php

// Check login state
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/session.php");
start_session();
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require($_SERVER["DOCUMENT_ROOT"] . "/config.php");

// Get point system transformer
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/point-system.php");

// DB Connection
$con = mysqli_connect(
    $config["db"]["credentials"]["host"],
    $config["db"]["credentials"]["user"],
    $config["db"]["credentials"]["password"],
    $config["db"]["credentials"]["name"]
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Get sorting
$sorting = $_SESSION["setting_sorting"];
if ($sorting == "average") $sorting_appendix = " ORDER BY average ASC";
else if ($sorting == "alphabet") $sorting_appendix = " ORDER BY name ASC";
else if ($sorting == "lastuse") $sorting_appendix = " ORDER BY last_used DESC";

// Get all subjects
$subjectlist = array();
if ($stmt = $con->prepare("SELECT name, color, id, last_used, average FROM " . $config["db"]["tables"]["subjects"] . " WHERE user_id = ? AND year = ?" . $sorting_appendix)) {
    $stmt->bind_param("ss", $_SESSION["user_id"], $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->bind_result($subject_name, $subject_color, $subject_id, $subject_last_used, $subject_grade_average);
    while ($stmt->fetch()) {
        $subjectlist[] = array(
            "name" => $subject_name,
            "color" => $subject_color,
            "id" => $subject_id,
            "last_used" => $subject_last_used,
            "average" => $subject_grade_average
        );
    }
    $stmt->close();
}

// DB Con close
$con->close();

// var_dump($subjectlist);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>subjects | Noten-App</title>
    <link rel="stylesheet" href="/res/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="/res/css/fonts.css">
    <link rel="stylesheet" href="/res/css/main.css">
    <link rel="stylesheet" href="/res/css/navbar.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/res/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/res/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/res/img/favicon-16x16.png">
    <link rel="mask-icon" href="/res/img/safari-pinned-tab.svg" color="#eb660e">
    <link rel="shortcut icon" href="/res/img/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Noten-App">
    <meta name="application-name" content="Noten-App">
    <meta name="msapplication-TileColor" content="#282c36">
    <meta name="msapplication-TileImage" content="/res/img/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/app.webmanifest">
    <meta name="msapplication-config" content="/browserconfig.xml">
</head>

<body>
    <nav>
        <a href="/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-home"></i>
            </div>
        </a>
        <a href="/homework/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-calendar-check"></i>
            </div>
        </a>
        <a href="/subjects/" class="nav-link nav-active">
            <div class="navbar_icon">
                <i class="fas fa-book"></i>
            </div>
        </a>
    </nav>
    <main id="main">
        <div class="subject_list">
            <?php
            foreach ($subjectlist as $subject) {
                echo '<div class="subject_entry';
                echo '" onclick="location.assign(\'./grades/?subject=' . $subject["id"] . '\')" style="border-color:#' . $subject["color"] . '">';
                echo '<div class="subject_entry-name">' . $subject["name"] . '</div>';
                if ($subject["average"] != 0) {
                    echo '<div class="subject_entry-average"> &empty; ';
                    if (systemRun("punkte")) echo (number_format(calcToPoints(false, $subject["average"]), $_SESSION["setting_rounding"], '.', ''));
                    else echo number_format($subject["average"], $_SESSION["setting_rounding"], '.', '');
                    echo '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
        <div class="subject_add" onclick="location.assign('/subjects/add/')">
            <div>Create subject <i class="fas fa-plus"></i></div>
        </div>
    </main>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <?php if ($config["tracking"]["matomo"]["on"]) echo ($config["tracking"]["matomo"]["code"]); ?>
</body>

</html>