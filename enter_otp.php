<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter OTP</title>
    <script>
        let expiry = <?php echo $_SESSION['otp_expiry'] ?? time(); ?>;
        function countdown() {
            const timer = document.getElementById("timer");
            const now = Math.floor(Date.now() / 1000);
            let secondsLeft = expiry - now;

            if (secondsLeft <= 0) {
                timer.innerText = "OTP expired.";
                document.getElementById("otp-form").style.display = "none";
                return;
            }

            let min = Math.floor(secondsLeft / 60);
            let sec = secondsLeft % 60;
            timer.innerText = `OTP expires in ${min}:${sec < 10 ? '0' : ''}${sec}`;

            setTimeout(countdown, 1000);
        }

        window.onload = countdown;
    </script>
</head>
<body>
    <h2>Enter OTP sent to your email</h2>
    <div id="timer"></div>
    <br>

    <form id="otp-form" action="verify_otp.php" method="post">
        <input type="text" name="otp" placeholder="Enter OTP" required>
        <button type="submit">Verify</button>
    </form>

    <br>
    <form action="resend_otp.php" method="post">
        <button type="submit">Resend OTP</button>
    </form>
</body>
</html>
