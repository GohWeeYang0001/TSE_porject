<?php
session_start();

if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("Location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];
include("../connection.php");

if (isset($_GET["id"])) {
    $appoid = intval($_GET["id"]);

    // 先查出这个预约对应的 scheduleid，确保该预约属于当前患者
    $sql = "SELECT scheduleid FROM appointment WHERE appoid = ? AND pid = (SELECT pid FROM patient WHERE pemail = ?)";
    $stmt = $database->prepare($sql);
    if ($stmt === false) {
        header("Location: appointment.php?error=DatabaseError");
        exit();
    }
    $stmt->bind_param("is", $appoid, $useremail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // 预约不存在或无权限
        header("Location: appointment.php?error=BookingNotFound");
        exit();
    }

    $row = $result->fetch_assoc();
    $scheduleid = $row['scheduleid'];
    $stmt->close();

    // 删除预约记录
    $sql_del = "DELETE FROM appointment WHERE appoid = ? AND pid = (SELECT pid FROM patient WHERE pemail = ?)";
    $stmt_del = $database->prepare($sql_del);
    if ($stmt_del === false) {
        header("Location: appointment.php?error=DatabaseError");
        exit();
    }
    $stmt_del->bind_param("is", $appoid, $useremail);
    $stmt_del->execute();

    if ($stmt_del->affected_rows > 0) {
        // 删除成功，nop +1
        $sql_update = "UPDATE schedule SET nop = nop + 1 WHERE scheduleid = ?";
        $stmt_update = $database->prepare($sql_update);
        if ($stmt_update === false) {
            header("Location: appointment.php?error=DatabaseError");
            exit();
        }
        $stmt_update->bind_param("i", $scheduleid);
        $stmt_update->execute();
        $stmt_update->close();

        header("Location: appointment.php?message=BookingCancelled");
        exit();
    } else {
        // 删除失败
        header("Location: appointment.php?error=BookingNotFound");
        exit();
    }

    $stmt_del->close();
} else {
    // 请求无效
    header("Location: appointment.php?error=InvalidRequest");
    exit();
}

$database->close();
?>
