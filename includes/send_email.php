<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/SMTP.php';
require_once __DIR__ . '/../PHPMailer/Exception.php';

function sendSuggestionEmail($name, $email, $category, $message)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'abdulsinglis03@gmail.com';
        $mail->Password = 'ptpz grxp lvhn feds';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('abdulsinglis03@gmail.com', 'Swahilipot Suggestion Box');
        $mail->addAddress('abdulsinglis03@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New Suggestion Received';

        $mail->Body = "
        <h2>New Suggestion</h2>
        <p><strong>Name:</strong> {$name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Category:</strong> {$category}</p>
        <p><strong>Message:</strong><br>{$message}</p>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {
        return false;
    }
}