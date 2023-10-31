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

// Get all subjects
$subjectlist = array();
if ($stmt = $con->prepare("SELECT name, color, id, last_used, average FROM " . $config["db"]["tables"]["subjects"] . " WHERE user_id = ? AND year = ? ORDER BY average ASC")) {
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

// Get year title
if ($stmt = $con->prepare("SELECT name FROM " . config_table_name_school_years . " WHERE id = ?")) {
    $stmt->bind_param("s", $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->bind_result($year_name);
    $stmt->fetch();
    $stmt->close();
}

// Create PDF
require('../res/php/fpdf185/fpdf.php');
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// - - - - - - - //
//    Header     //
// - - - - - - - //
$pdf->Image('../res/img/logo.png', 10, 6, 20);
$pdf->SetFont('Arial', 'B', 15);
$pdf->Cell(70);
$pdf->Cell(60, 10, 'Noten-App.de - Export (' . $year_name . ')', 0, 0, 'C');
$pdf->Ln(20);

// - - - - - - - //
//    Grades     //
// - - - - - - - //
foreach ($subjectlist as $subject) {
    // Subject-Header
    $pdf->SetFont('Arial', 'B', 15);
    if ($subject["average"] != 0) $title = $subject["name"] . ' - ' . iconv('utf-8', 'cp1252', 'Ø ') . number_format($subject["average"], $_SESSION["setting_rounding"], '.', '');
    else $title = $subject["name"];
    $pdf->Cell(0, 10, $title, 0, 0, 'L');
    $pdf->Ln(7.5);
    $pdf->SetTextColor(0, 0, 0);
    // Table-Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(30, 10, 'Datum', 1, 0, 'C');
    $pdf->Cell(20, 10, 'Note', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Art der Note', 1, 0, 'C');
    $pdf->Cell(100, 10, 'Notiz', 1, 0, 'C');
    $pdf->Ln();
    // Get grades
    $grades = array();
    if ($stmt = $con->prepare('SELECT id, user_id, subject, note, type, date, grade FROM grades WHERE subject = ?')) {
        $stmt->bind_param('s', $subject["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        foreach ($result as $row) {
            array_push($grades, $row);
        }
        $stmt->close();
    } else {
        exit("ERROR1");
    }
    $pdf->SetFont('Arial', '', 12);
    foreach ($grades as $grade) {
        $pdf->Cell(30, 10, $grade["date"], 1, 0, 'C');
        $pdf->Cell(20, 10, $grade["grade"], 1, 0, 'C');
        switch ($grade["type"]) {
            case "k":
                $grade["type"] = "Exam";
                break;
            case "m":
                $grade["type"] = "Verbal";
                break;
            case 's':
                $grade["type"] = "Other";
                break;
            case 't':
                $grade["type"] = "Test";
                break;
            default:
                $grade["type"] = "Unknown";
                break;
        }
        $pdf->Cell(40, 10, $grade["type"], 1, 0, 'C');
        $pdf->Cell(100, 10, iconv('utf-8', 'cp1252', $grade["note"]), 1, 0, 'C');
        $pdf->Ln();
    }
    // Linebreak
    $pdf->Ln(5);
}

// DB Con close
$con->close();

// Output PDF
$pdf->Output("d", "Export-" . date("d_m_Y") . ".pdf");
