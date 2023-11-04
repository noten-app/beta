<?php

// Check if subject url-parameter is given
if (!isset($_GET["subject"])) header("Location: /subjects");
$subject_id = htmlspecialchars($_GET["subject"]);
// Check if subject is a-z or 0-9
if (!preg_match("/^[a-z0-9]*$/", $subject_id)) header("Location: /subjects");

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

// Get subject
if ($stmt = $con->prepare('SELECT name, color, user_id, last_used, grade_k, grade_m, grade_t, grade_s FROM subjects WHERE id = ?')) {
    $stmt->bind_param('s', $subject_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($subject_name, $subject_color, $user_id, $last_used, $grade_k, $grade_m, $grade_t, $grade_s);
    $stmt->fetch();
    if ($user_id !== $_SESSION["user_id"]) {
        $name = "";
        $color = "";
        $user_id = "";
        $last_used = "";
        exit("ERROR2");
    }
    $stmt->close();
} else {
    exit("ERROR1");
}

// DB Con close
$con->close();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $subject_name ?> | Noten-App</title>
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
        <a href="/subjects/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-arrow-left"></i>
            </div>
        </a>
        <div class="nav-link" id="save_subject">
            <div class="navbar_icon navbar_icon-save">
                <i class="fa-solid fa-floppy-disk"></i>
            </div>
        </div>
    </nav>
    <main id="main">
        <div class="subject_title">
            <h1><?= $class_name ?></h1>
        </div>
        <div class="subject_edit">
            <i id="view_settings" class="fas fa-cog" onclick="location.assign('/subjects/grades?subject=<?= $class_id ?>')"></i>
        </div>
        <div class="subject-main_content">
            <div class="statistics main_view" id="main_view-statistics">
                STATS
            </div>
            <div class="settings main_view" id="main_view-settings">
                <div class="name">
                    <div class="name-title">
                        Subject-Name
                    </div>
                    <div class="name-container">
                        <input type="text" id="name-input" value="<?= $subject_name ?>">
                    </div>
                </div>
                <div class="grading">
                    <div class="grading-title">
                        Grading - General
                    </div>
                    <div class="grading-options">
                        <div class="grading-option">
                            <div class="grading-option-title">Exams</div>
                            <div class="grading-option-input">
                                <input type="number" id="grading_option-type_k" value="<?= $grade_k ?>" />
                            </div>
                        </div>
                        <div class="grading-option">
                            <div class="grading-option-title">Verbal</div>
                            <div class="grading-option-input">
                                <input type="number" id="grading_option-type_m" value="<?= $grade_m ?>" />
                            </div>
                        </div>
                        <div class="grading-option <?php if ($grade_t === "1exam") echo "grading-option-hidden" ?>" id="grading-option_tests">
                            <div class="grading-option-title">Tests</div>
                            <div class="grading-option-input">
                                <input type="number" id="grading_option-type_t" value="<?php if ($grade_t !== "1exam") echo $grade_t;
                                                                                        else echo 1; ?>" />
                            </div>
                        </div>
                        <div class="grading-option">
                            <div class="grading-option-title">Other</div>
                            <div class="grading-option-input">
                                <input type="number" id="grading_option-type_s" value="<?= $grade_s ?>" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="test-behaviour">
                    <div class="test-behaviour-title">
                        Grading - Test Behaviour
                    </div>
                    <div class="test-behaviour-switch">
                        <div class="button_divider">
                            <div class="<?php if ($grade_t === "1exam") echo "button_divider-button_active" ?>" id="test-behaviour-switch_all1">
                                All Tests<br>1 Exam
                            </div>
                            <div class="<?php if ($grade_t !== "1exam") echo "button_divider-button_active" ?>" id="test-behaviour-switch_custom">
                                Custom
                            </div>
                        </div>
                    </div>
                </div>
                <div class="color">
                    <div class="color-title">
                        Subject-Color
                    </div>
                    <div class="color-input">
                        <input type="color" id="color_input-input" value="#<?= $subject_color ?>">
                    </div>
                </div>
                <div class="delete dangerzone">
                    <div class="color-title">
                        Delete subject
                    </div>
                    <div class="delete-button">
                        <button onclick="deleteClass();">Delete</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="subjectID" style="display: none;"><?= $subject_id ?></div>
    </main>
    <script src="https://assets.noten-app.de/js/jquery/jquery-3.6.1.min.js"></script>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <script src="./test-switch.js"></script>
    <script src="./view-cycler.js"></script>
    <script src="./modify-subject.js"></script>
    <script src="./delete-subject.js"></script>
    <?php if ($config["tracking"]["matomo"]["on"]) echo ($config["tracking"]["matomo"]["code"]); ?>
</body>

</html>