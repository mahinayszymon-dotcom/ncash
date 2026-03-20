<?php
    session_start();
    include("../config/db_conn.php");

    // If they are already fully logged in, they shouldn't be here
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        header("Location: ../dashboard/home.php");
        exit();
    }

    $error_message = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
        $entered_email = trim($_POST['email']);

        // 1. Check if the email exists in the users table
        $sql = "SELECT user_id, email FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $entered_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $user_id = $user['user_id'];

            // 2. Generate a secure 6-digit OTP
            $otp = (string) random_int(100000, 999999);

            // 3. Clear any old, unused reset codes for this user to keep the DB clean
            $delete_old = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $delete_old->bind_param("i", $user_id);
            $delete_old->execute();
            $delete_old->close();

            // 4. Insert the new OTP into the dedicated password_resets table (Using MySQL Time)
            $insert_reset = $conn->prepare("INSERT INTO password_resets (user_id, otp_code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
            $insert_reset->bind_param("is", $user_id, $otp);
            $insert_reset->execute();
            $insert_reset->close();

            // 5. Send the email using your helper
            require_once("../resources/external/phpmailer/mail_helper.php"); 
            $subject = "TraceMo - Password Reset Request";
            $message = "<h2>Your password reset code is: <span style='color: #d93025;'>$otp</span></h2><p>This code will expire in 10 minutes. If you did not request this, please ignore this email.</p>";
            sendBusinessEmail($user['email'], $subject, $message);

            // 6. Set TEMPORARY sessions and set the ACTION flag to 'password_reset'
            $_SESSION['temp_user_id'] = $user_id;
            $_SESSION['temp_email'] = $user['email'];
            $_SESSION['otp_action'] = 'password_reset'; // The router flag!

            // 7. Send them to the OTP page
            header("Location: verify_otp.php");
            exit();

        } else {
            // Security Best Practice: Don't explicitly say "Email not found".
            $error_message = "If that email exists in our system, a code has been sent.";
        }
        $stmt->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forget Password</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/auth/non-login.css">
    <!-- <link rel="stylesheet" href="../resources/css/pages/auth/login.css"> -->
</head>
<body>
    <button onclick="window.location.href='login.php'" id="return"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
    <section class="main2">
        <div class="forget_pass_graphic">
            <div class="graphic_img">
                <img src="../resources/img/icons/sample.png" alt="sample">
            </div>
            <div class="graphic_text">
                <h2>Track, Manage, and Notify with TraceMo</h2>
                <p>Everything your business needs for accurate records and timely client updates.</p>
            </div>
        </div>
        <div class="forget_pass_panel">
            <div class="forget_pass_panel_container">
                <div class="logo">
                    <img src="../resources/img/ncash_logo_login.png" alt="N-Cash Logo">
                </div>
                <div class="login_header">
                    <h1>Forget Password</h1>
                    <p>Input your email to send an OTP.</p>
                </div>
                <form action="" method="POST">
                    <div class="email_input">   
                        <div class="input_container">
                            <input type="email" name="email" id="email" placeholder="user@gmail.com" autocomplete="off" required>
                            <img src="../resources/img/icons/mail.png" alt="email" class="left_icon1">
                            <img src="../resources/img/icons/mail_c.png" alt="email" class="left_icon2">
                        </div>
                        <div class="error_container" style="<?php echo empty($error_message) ? 'display:none;' : 'display:block;'; ?>">
                            <div class="error" style="<?php echo empty($error_message) ? 'display:none;' : 'display:flex;'; ?>">
                                <img src="../resources/img/icons/error.png" alt="error">
                                <span><?php echo htmlspecialchars($error_message); ?></span>
                                <!--
                                <span>An error occured. Please refresh this page and try again.</span>
                                <span>Username not found.</span>
                                -->
                            </div>             
                        </div>                          
                    </div>
                    <div class="send_otp_btn_cont">
                        <button type="submit" name="send">Send OTP</button>
                    </div>
                </form>
                <div class="forget_pass_footer">
                    <p>© 2026 TraceMo · N-Cash Luxury Pawnshop | Developed by C&C</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>