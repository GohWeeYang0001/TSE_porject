<?php
ob_start();
require_once(__DIR__ . '/TCPDF-main/tcpdf.php');
include("../connection.php");

// 初始化 PDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('DocAP System');
$pdf->SetTitle('Patient Appointments');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 11);

// Logo + 标题
$logoPath = __DIR__ . '/../img/logo.png';
$header = '
<table cellspacing="0" cellpadding="5">
    <tr>
        <td width="15%"><img src="' . $logoPath . '" width="50" /></td>
        <td width="85%" style="font-size:18px; font-weight:bold; color:#007BFF;">DocAP - Patient Appointment Records</td>
    </tr>
</table><hr style="height:1px;border:none;color:#ccc;background-color:#ccc;" /><br>';
$pdf->writeHTML($header, true, false, true, false, '');

// 查询所有患者预约记录
$query = "
    SELECT p.pname, p.pemail, p.paddress, p.ptel,
           a.apponum,
           d.docname,
           s2.sname,
           sch.scheduledate,
           sch.scheduletime
    FROM appointment a
    LEFT JOIN patient p ON a.pid = p.pid
    LEFT JOIN schedule sch ON a.scheduleid = sch.scheduleid
    LEFT JOIN doctor d ON sch.docid = d.docid
    LEFT JOIN specialties s2 ON d.specialties = s2.id
    ORDER BY p.pname, a.apponum
";

$result = mysqli_query($database, $query);

// 构造表格
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
    <th>Patient Name</th>
    <th>Email</th>
    <th>Address</th>
    <th>Phone</th>
    <th>Appoint #</th>
    <th>Doctor</th>
    <th>Specialty</th>
    <th>Date</th>
    <th>Time</th>
</tr>';

while ($row = mysqli_fetch_assoc($result)) {
    $tableHTML .= "<tr>
        <td>{$row['pname']}</td>
        <td>{$row['pemail']}</td>
        <td>{$row['paddress']}</td>
        <td>{$row['ptel']}</td>
        <td align='center'>{$row['apponum']}</td>
        <td>{$row['docname']}</td>
        <td>{$row['sname']}</td>
        <td>{$row['scheduledate']}</td>
        <td>{$row['scheduletime']}</td>
    </tr>";
}

$tableHTML .= "</table>";
$pdf->writeHTML($tableHTML, true, false, true, false, '');

ob_end_clean();
$pdf->Output('patient_appointments.pdf', 'D');
?>
