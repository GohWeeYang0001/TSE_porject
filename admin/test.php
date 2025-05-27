<?php

use PHPMailer\PHPMailer\PHPMailer;

require '../admin/PHPMailer-master/src/PHPMailer.php';
require '../admin/PHPMailer-master/src/SMTP.php';
require '../admin/PHPMailer-master/src/Exception.php';


$mail = new PHPMailer(true);

try {
    // 配置 SMTP
    $mail->SMTPDebug = 2;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'jiunendarren@gmail.com';
    $mail->Password = 'lmixekratteruehs';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;


    // 发件人和收件人
    $mail->setFrom('jiunendarren@gmail.com', 'DocAP Test');
    $mail->addAddress('jiunendarren@gmail.com', 'Darren'); // 改为你想测试的收件人

    // 邮件内容
    $mail->isHTML(true);
    $mail->Subject = '📧 DocAP Test Email';
    $mail->Body    = 'This is a <strong>test email</strong> sent from PHPMailer setup. Everything works!';

    $mail->send();
    echo "✅ Test email sent successfully.";
} catch (Exception $e) {
    echo "❌ Test email failed. Error: {$mail->ErrorInfo}";
}
