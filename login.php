<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servername = "localhost";
    $username   = "root";
    $password   = "password";
    $dbname     = "crypto_transaction";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'] ?? '';
    $pwd   = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, email, password_hash FROM users WHERE email = ?");
    if (!$stmt) {
        die("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($pwd, $user['password_hash'])) {

            // OTP generation
            $otp = rand(100000, 999999);
            $_SESSION['pending_user_id'] = $user['id'];
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $user['email'];
            $_SESSION['otp_expiry'] = time() + 120; 
            $_SESSION['otp_attempts'] = 0;          
            
            // Send OTP via email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'email@gmail.com'; 
                $mail->Password = 'app password';   
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'Crypto Trading Platform');
                $mail->addAddress($user['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Your OTP Code';
                $mail->Body    = "Your OTP is <b>$otp</b>. It expires in 2 minutes.";

                $mail->send();
                header("Location: enter_otp.php");
                exit;
            } catch (Exception $e) {
                echo "OTP email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }

        } else {
            echo "Invalid credentials!";
        }
    } else {
        echo "No user found with that email!";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
    exit;
}
