<?php
date_default_timezone_set('Asia/Kuala_Lumpur');
require '../connection.php';
require '../admin/PHPMailer-master/src/PHPMailer.php';
require '../admin/PHPMailer-master/src/SMTP.php';
require '../admin/PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$today = date("Y-m-d");

$query = "
    SELECT a.appoid, p.pname, p.pemail, sch.scheduledate, sch.scheduletime, d.docname
    FROM appointment a
    JOIN patient p ON a.pid = p.pid
    JOIN schedule sch ON a.scheduleid = sch.scheduleid
    JOIN doctor d ON sch.docid = d.docid
    WHERE sch.scheduledate = '$today'
      AND TIME_TO_SEC(TIMEDIFF(sch.scheduletime, CURTIME())) BETWEEN 0 AND 10800
      AND a.email_sent = 0
";

$result = mysqli_query($database, $query);

while ($row = mysqli_fetch_assoc($result)) {
    $success = false;
    $attempts = 0;

    while (!$success && $attempts < 2) {
        $attempts++;
        $mail = new PHPMailer(true);

        try {
            // 配置 SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jiunendarren@gmail.com';
            $mail->Password = 'lmixekratteruehs';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // 发件信息
            $mail->setFrom('jiunendarren@gmail.com', 'DocAP');
            $mail->addAddress($row['pemail'], $row['pname']);

            // 邮件内容
            $mail->isHTML(true);
            $mail->Subject = 'Appointment Reminder';
            $mail->Body    = "
                Hi {$row['pname']},<br><br>
                This is a friendly reminder that you have an appointment with Dr. {$row['docname']} today at <strong>{$row['scheduletime']}</strong>.<br><br>
                Please be on time.<br><br>
                Best regards,<br>
                DocAP Team
            ";

            $mail->send();
            echo "✅ Email sent to {$row['pemail']}<br>";

            // 更新邮件发送状态 + 显示反馈
            $updateQuery = "UPDATE appointment SET email_sent = 1 WHERE appoid = {$row['appoid']}";
            if (!mysqli_query($database, $updateQuery)) {
                echo "❌ Failed to update email_sent for appoid {$row['appoid']}: " . mysqli_error($database) . "<br>";
            } else {
                echo "🟢 email_sent updated for appoid {$row['appoid']}<br>";
            }

            $success = true;
        } catch (Exception $e) {
            echo "❌ Attempt $attempts failed for {$row['pemail']}. Error: {$mail->ErrorInfo}<br>";
        }
    }
}
?>
