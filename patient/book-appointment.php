<?php
session_start();

if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

// 获取用户信息
$stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userfetch = $stmt->get_result()->fetch_assoc();
$pid = $userfetch["pid"];

if ($_POST) {
    $docid = $_POST["docid"];
    $booking_time = $_POST["booking_time"];
    $appodate = $_POST["appodate"];
    $pid = $_POST["pid"];

    // 生成新的预约编号
    $stmt = $database->prepare("SELECT MAX(apponum) AS max_num FROM appointment WHERE docid = ? AND appodate = ?");
    $stmt->bind_param("is", $docid, $appodate);
    $stmt->execute();
    $max_num = $stmt->get_result()->fetch_assoc()['max_num'] ?? 0;
    $apponum = $max_num + 1;

    // 检查时间是否已被预订
    $stmt = $database->prepare("SELECT COUNT(*) AS count FROM appointment WHERE docid = ? AND appodate = ? AND time = ?");
    $stmt->bind_param("iss", $docid, $appodate, $booking_time);
    $stmt->execute();
    $count = $stmt->get_result()->fetch_assoc()['count'];

    if ($count > 0) {
        header("location: appointment.php?error=TimeAlreadyBooked");
        exit();
    }

    // 插入新预约
    $stmt = $database->prepare("INSERT INTO appointment (pid, apponum, appodate, docid, time) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisis", $pid, $apponum, $appodate, $docid, $booking_time);
    $stmt->execute();
    $appoid = $database->insert_id;

    header("location: appointment.php?action=booking-added&id=$appoid");
}
?>