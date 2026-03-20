<?php
    session_start();
    include("../config/db_conn.php");
    
    require_once("../resources/external/phpmailer/mail_helper.php"); 

    // 1. If they are already fully logged in, send them to the dashboard
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        header("Location: ../dashboard/home.php");
        exit();
    } 
    // 2. VERY IMPORTANT: Did they actually pass the OTP check? 
    else if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['can_reset_password']) || $_SESSION['can_reset_password'] !== true) {
        // If not, kick them out!
        header("Location: ../auth/denied.php");
        exit();
    }

    $error_message = "";

    // 3. Process the new password submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password']) && isset($_POST['confirm_new_password'])) {
        
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_new_password'];
        $user_id = $_SESSION['temp_user_id'];
        $user_email = $_SESSION['temp_email']; // Grab the email we saved earlier!

        if ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match. Please try again.";
        } 
        else if (strlen($new_password) < 12) {
            $error_message = "Password must be at least 12 characters long.";
        } 
        else {
            // Hash the new password securely
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                
                // --- email ---
                $subject = "TraceMo - Password Changed Successfully";
                $message = "
                    <h2>Your Password Has Been Changed</h2>
                    <p>This email is to confirm that the password for your TraceMo account has been successfully updated.</p>
                    <div style='background-color: #fff3f3; padding: 15px; border-left: 4px solid #d93025; margin-top: 20px;'>
                        <p style='color: #d93025; margin: 0; font-weight: bold;'>Security Alert:</p>
                        <p style='margin-top: 5px; color: #333;'>If you did not authorize this change, please contact your system administrator or N-Cash support immediately to secure your account.</p>
                    </div>
                ";
                sendBusinessEmail($user_email, $subject, $message);
                // ------------------------------------

                // Destroy the temporary sessions so they can't reset it again without a new OTP
                session_unset(); 
                session_destroy();

                // Start a fresh session just to show the success message on the login page
                session_start();
                $_SESSION['register_success_msg'] = "Password changed successfully! You can now log in.";
                
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Database error. Failed to update password.";
            }
            $stmt->close();
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/auth/non-login.css">
    <!-- <link rel="stylesheet" href="../resources/css/pages/auth/login.css"> -->
</head>
<body>
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
                    <h1>Change Password</h1>
                    <p>Finalize your account recovery by setting a new secure password.</p>
                </div>
                <form action="" method="POST">
                    <div class="email_input"> 
                        <div class="top_content">
                            <span class="message_info"><img src="../resources/img/icons/bulb.png" alt="info">Password should be at least 12 characters long, and include a mix of uppercase and lowercase letters, numbers, and symbols.</span>
                        </div>
                        <br>
                        <div class="input_container">
                            <label for="new_password">New Password</label>
                        </div>  
                        <div class="input_container">
                            <input type="password" name="new_password" id="new_password" autocomplete="off" required>
                            <img src="../resources/img/icons/password.png" alt="pin" class="left_icon1">
                            <img src="../resources/img/icons/password_c.png" alt="pin" class="left_icon2">
                        </div>
                        <div class="input_container">
                            <label for="confirm_new_password">Confirm New Password</label>
                        </div>  
                        <div class="input_container">
                            <input type="password" name="confirm_new_password" id="confirm_new_password" autocomplete="off" required>
                            <img src="../resources/img/icons/password.png" alt="pin" class="left_icon1">
                            <img src="../resources/img/icons/password_c.png" alt="pin" class="left_icon2">
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
                        <button type="submit" name="send">Change Password</button>
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