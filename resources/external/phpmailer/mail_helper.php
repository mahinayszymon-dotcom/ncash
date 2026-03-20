<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendBusinessEmail($toEmail, $subject, $messageBody) {
    // Path to your manual installation
    $path = __DIR__ . '/PHPMailer-master/src/';

    require_once $path . 'Exception.php';
    require_once $path . 'PHPMailer.php';
    require_once $path . 'SMTP.php';

    $mail = new PHPMailer(true);

    try {
        // --- Server Settings ---
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Port = 587;
        $mail->Username = 'ncash.official.info@gmail.com';
        $mail->Password = 'vqtwkixwprldzotp';

        // --- Recipients ---
        $mail->setFrom('ncash.official.info@gmail.com', 'N-Cash Luxury Pawnshop');
        $mail->addAddress($toEmail);

        // This tells the email client NOT to reply to the sender
        $mail->addReplyTo('no-reply@gmail.com', 'No Reply');

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;
        $mail->AltBody = strip_tags($messageBody); // Plain text version

        return $mail->send();
    } catch (Exception $e) {
        // For testing, we return the error message
        return "Error: " . $mail->ErrorInfo;
    }
}