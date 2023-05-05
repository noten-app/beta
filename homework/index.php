<?php 

    // Check login state
    session_start();
    require("../res/php/checkLogin.php");
    if(!checkLogin()) header("Location: /account");

    // Get config
    require("../config.php");

    // DB Connection
    $con = mysqli_connect(
        config_db_host,
        config_db_user,
        config_db_password,
        config_db_name
    );
    if(mysqli_connect_errno()) exit("Error with the Database");

    // Get all tasks
    if($stmt = $con->prepare("SELECT * FROM ".config_table_name_homework." WHERE user_id = ?")) {
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $homework = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Get all classes
    if($stmt = $con->prepare("SELECT * FROM ".config_table_name_classes." WHERE user_id = ?")) {
        $stmt->bind_param("i", $_SESSION["user_id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $classes = $result->fetch_all(MYSQLI_ASSOC);
    }

    // Order homework by deadline in arrays - arrays by date
    $homework_ordered = array();
    foreach($homework as $task) {
        if(!array_key_exists($task["deadline"], $homework_ordered)) {
            $homework_ordered[$task["deadline"]] = array();
        }
        $homework_ordered[$task["deadline"]][] = $task;
    }
    ksort($homework_ordered);

    // DB Con close
    $con->close();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework - NotenApp</title>
    <link rel="stylesheet" href="/res/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/regular.min.css">
    <link rel="stylesheet" href="/res/css/fonts.css">
    <link rel="stylesheet" href="/res/css/main.css">
    <link rel="stylesheet" href="/res/css/navbar.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/res/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/res/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/res/img/favicon-16x16.png">
    <link rel="mask-icon" href="/res/img/safari-pinned-tab.svg" color="#eb660e">
    <link rel="shortcut icon" href="/res/img/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="NotenApp">
    <meta name="application-name" content="NotenApp">
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
        <?php if($_SESSION["beta_tester"] == 1){ ?>
        <a href="/calendar/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </a>
        <?php } if($_SESSION["beta_tester"] == 1){ ?>
        <a href="/homework/" class="nav-link nav-active">
            <div class="navbar_icon">
                <i class="fas fa-calendar-check"></i>
            </div>
        </a>
        
        <?php } ?>
        <a href="/classes/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-book"></i>
            </div>
        </a>
    </nav>
    <main id="main">
        <div class="homework_list">
            <?php 
            // foreach ($homework as $hw_entry) {
            //     echo '<div class="homework_entry">';
            //     echo '<div class="classname">';
            //     foreach ($classes as $class) if ($class["id"] == $hw_entry["class"]) echo $class["name"];
            //     echo '</div><div class="deadline">'.$hw_entry["deadline"].'</div>';
            //     echo '<div class="task">'.$hw_entry["text"].'</div>';
            //     echo '<div class="dot"><i class="fa-regular fa-circle"></i></div>';
            //     echo '</div>';
            // }
            foreach($homework_ordered as $hw_dategroup){
                echo '<div class="homework_deadline';
                if(strtotime($hw_dategroup[0]["deadline"]) < strtotime("today")) echo ' homework_deadline_late';
                if(strtotime($hw_dategroup[0]["deadline"]) == strtotime("today")) echo ' homework_deadline_soon';
                echo '">';
                echo '<div class="homework_deadline_date">'.date("d.m - l", strtotime($hw_dategroup[0]["deadline"])).'</div>';
                echo '<div class="homework_deadline_tasks">';
                foreach ($hw_dategroup as $hw_entry) {
                    echo '<div class="homework_entry">';
                    echo '<div class="classname">';
                    foreach ($classes as $class) if ($class["id"] == $hw_entry["class"]) echo $class["name"];
                    echo '</div><div class="task">'.$hw_entry["text"].'</div>';
                    echo '<div class="dot"><i class="fa-regular fa-circle"></i></div>';
                    switch ($hw_entry["type"]) {
                        case 'b':
                            echo '<div class="type_badge"><i class="fa-solid fa-book"></i></div>';
                            break;
                        case 'w':
                            echo '<div class="type_badge"><i class="fa-solid fa-sheet-plastic"></i></div>';
                            break;
                        case 'v':
                            echo '<div class="type_badge"><i class="fa-solid fa-language"></i></div>';
                            break;
                    }
                    echo '</div>';
                }
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
        <div class="homework_add" onclick="location.assign('./add/')">
            <div>Add homework <i class="fas fa-plus"></i></div>
        </div>
    </main>
    <script src="/res/js/themes/themes.js"></script>
</body>

</html>