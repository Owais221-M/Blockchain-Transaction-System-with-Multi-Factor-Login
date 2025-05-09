<?php
session_start();

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['otp_email']) || !isset($_SESSION['pending_user_id'])) {
    echo "Session expired. Please log in again.";
    exit;
}

// Regenerate OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_expiry'] = time() + 120;
$_SESSION['otp_attempts'] = 0;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'ansariowais616@gmail.com';
    $mail->Password = 'cibjqgqsmzehakzf';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your_email@gmail.com', 'Crypto Trading Platform');
    $mail->addAddress($_SESSION['otp_email']);
    $mail->isHTML(true);
    $mail->Subject = 'Your NEW OTP Code';
    $mail->Body    = "Your new OTP is <b>$otp</b>. It expires in 2 minutes.";

    $mail->send();

    header("Location: enter_otp.php");
    exit;

} catch (Exception $e) {
    echo "Could not resend OTP. Mailer Error: {$mail->ErrorInfo}";
}
