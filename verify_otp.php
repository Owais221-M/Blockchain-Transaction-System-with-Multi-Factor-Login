<?php
session_start();

$entered_otp = $_POST['otp'];

if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expiry']) || !isset($_SESSION['pending_user_id'])) {
    echo "Session expired or unauthorized access. Please login again.";
    session_destroy();
    exit;
}

// Check expiration
if (time() > $_SESSION['otp_expiry']) {
    echo "OTP expired. Please login again.";
    session_destroy();
    exit;
}

// Count attempts
if (!isset($_SESSION['otp_attempts'])) {
    $_SESSION['otp_attempts'] = 0;
}

$_SESSION['otp_attempts']++;

// Reject if attempts exceeded
if ($_SESSION['otp_attempts'] > 3) {
    echo "Too many incorrect attempts. Please login again.";
    session_destroy();
    exit;
}

if ($entered_otp == $_SESSION['otp']) {
    $_SESSION['id'] = $_SESSION['pending_user_id'];

    // Clean up
    unset($_SESSION['otp'], $_SESSION['otp_expiry'], $_SESSION['otp_email'], $_SESSION['otp_attempts'], $_SESSION['pending_user_id']);

    header("Location: dashboard.php");
    exit;
} else {
    $remaining = 3 - $_SESSION['otp_attempts'];
    echo "Incorrect OTP. You have $remaining attempt(s) left.";
}
?>
