<?php
ob_start();
require_once(__DIR__ . '/TCPDF-main/tcpdf.php');
include("../connection.php");

// Create TCPDF instance
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('DocAP System');
$pdf->SetTitle('Doctor Appointment Summary');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Logo Path & System Name
$logoPath = __DIR__ . '../img/logo.png';
$systemName = "DocAP";

// Header Section (Logo + Title)
$header = '
<table cellspacing="0" cellpadding="5">
    <tr>
        <td width="15%"><img src="' . $logoPath . '" width="50" /></td>
        <td width="85%" style="font-size:18px; font-weight:bold; color:#007BFF;">' . $systemName . ' - Doctor Appointment Summary</td>
    </tr>
</table><hr style="height:1px;border:none;color:#ccc;background-color:#ccc;" /><br>
';
$pdf->writeHTML($header, true, false, true, false, '');

// Get the selected doctor from POST, default to 'all' for all doctors
$doctorId = isset($_POST['doctor']) ? $_POST['doctor'] : 'all';

// Get the current date
$today = date("Y-m-d");

// Query to get the total number of doctors and appointments
$doc_result = mysqli_query($database, "SELECT COUNT(*) as doc_count FROM doctor");
$app_result = mysqli_query($database, "SELECT COUNT(*) as app_count FROM appointment");
$doc_count = mysqli_fetch_assoc($doc_result)['doc_count'];
$app_count = mysqli_fetch_assoc($app_result)['app_count'];

// Summary Section
$summaryHTML = "
<table cellpadding=\"6\">
    <tr>
        <td><strong>Date:</strong> $today</td>
    </tr>
    <tr>
        <td><strong>Total Doctors:</strong> $doc_count</td>
    </tr>
    <tr>
        <td><strong>Total Appointments:</strong> $app_count</td>
    </tr>
</table><br><br>
";
$pdf->writeHTML($summaryHTML, true, false, true, false, '');

// Table Header + Data Table
$tableHTML = '
<style>
    th {
        background-color: #007BFF;
        color: #fff;
        font-weight: bold;
        text-align: center;
    }
    td {
        border: 1px solid #ccc;
        padding: 5px;
    }
</style>
<table cellpadding="5" border="1">
<tr>
    <th width="20%">Name</th>
    <th width="25%">Email</th>
    <th width="15%">Tel</th>
    <th width="20%">Specialty</th>
    <th width="20%">Appointment Count</th>
</tr>';

// Modify the query based on the selected doctor
if ($doctorId != 'all') {
    $query = "
        SELECT d.docname, d.docemail, d.doctel, s.sname, COUNT(a.appoid) AS appointment_count
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        LEFT JOIN schedule sch ON d.docid = sch.docid
        LEFT JOIN appointment a ON sch.scheduleid = a.scheduleid
        WHERE d.docid = $doctorId
        GROUP BY d.docid
    ";
} else {
    $query = "
        SELECT d.docname, d.docemail, d.doctel, s.sname, COUNT(a.appoid) AS appointment_count
        FROM doctor d
        LEFT JOIN specialties s ON d.specialties = s.id
        LEFT JOIN schedule sch ON d.docid = sch.docid
        LEFT JOIN appointment a ON sch.scheduleid = a.scheduleid
        GROUP BY d.docid
    ";
}

// Execute the query
$result = mysqli_query($database, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $tableHTML .= "<tr>
        <td>{$row['docname']}</td>
        <td>{$row['docemail']}</td>
        <td>{$row['doctel']}</td>
        <td>{$row['sname']}</td>
        <td align=\"center\">{$row['appointment_count']}</td>
    </tr>";
}

$tableHTML .= "</table>";
$pdf->writeHTML($tableHTML, true, false, true, false, '');

// Clean buffer and output PDF
ob_end_clean();
$pdf->Output('doctor_summary.pdf', 'D');
?>
