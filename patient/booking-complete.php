<?php

//learn from w3schools.com

session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}


//import database
include("../connection.php");
$sqlmain = "select * from patient where pemail=?";
$stmt = $database->prepare($sqlmain);
$stmt->bind_param("s", $useremail);
$stmt->execute();
$userrow = $stmt->get_result();
$userfetch = $userrow->fetch_assoc();
$userid = $userfetch["pid"];
$username = $userfetch["pname"];


if ($_POST) {
    if (isset($_POST["booknow"])) {
        $apponum = intval($_POST["apponum"]);
        $scheduleid = intval($_POST["scheduleid"]);
        $date = $_POST["date"];

        // 查询剩余名额
        $sql_check = "SELECT nop FROM schedule WHERE scheduleid = ?";
        $stmt_check = $database->prepare($sql_check);
        $stmt_check->bind_param("i", $scheduleid);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            echo "<script>alert('预约信息不存在！'); window.location='schedule.php';</script>";
            exit;
        }
        $row_check = $result_check->fetch_assoc();
        $nop = intval($row_check['nop']);
        if ($nop <= 0) {
            echo "<script>alert('该时段预约已满，无法预约。'); window.location='schedule.php';</script>";
            exit;
        }

        // 插入预约
        $sql2 = "INSERT INTO appointment(pid, apponum, scheduleid, appodate) VALUES (?, ?, ?, ?)";
        $stmt_insert = $database->prepare($sql2);
        $stmt_insert->bind_param("iiis", $userid, $apponum, $scheduleid, $date);
        $res_insert = $stmt_insert->execute();

        if ($res_insert) {
            // nop减1
            $sql_update = "UPDATE schedule SET nop = nop - 1 WHERE scheduleid = ?";
            $stmt_update = $database->prepare($sql_update);
            $stmt_update->bind_param("i", $scheduleid);
            $stmt_update->execute();

            header("location: appointment.php?action=booking-added&id=" . $apponum . "&titleget=none");
            exit;
        } else {
            echo "<script>alert('预约失败，请稍后再试。'); window.location='schedule.php';</script>";
            exit;
        }
    }
}
