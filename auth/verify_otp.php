<?php
    session_start();
    include("../config/db_conn.php");

    // If they are already fully logged in, send them to the dashboard
    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        header("Location: ../dashboard/home.php");
        exit();
    } 
    // Kick them out if they don't have a temporary session OR the action flag is missing
    else if (!isset($_SESSION['temp_user_id']) || !isset($_SESSION['otp_action'])) {
        header("Location: ../auth/denied.php");
        exit();
    } 

    $error_message = "";

    // FIX 1: We check for 'pin' instead of 'send' to guarantee the form processes!
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['pin'])) {
        
        $entered_pin = $_POST['pin'];
        $temp_user_id = $_SESSION['temp_user_id'];
        $action = $_SESSION['otp_action'];
        $is_valid = false; // We will flip this to true if the DB matches

        // -----------------------------------------------------
        // 1. CHECK THE CORRECT TABLE BASED ON THE ACTION
        // -----------------------------------------------------
        if ($action === '2fa_login') {
            
            $sql = "SELECT id FROM user_two_factor WHERE user_id = ? AND otp_code = ? AND otp_expires_at > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $temp_user_id, $entered_pin);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $is_valid = true;
                // Clear the 2FA OTP
                $clear_otp = $conn->prepare("UPDATE user_two_factor SET otp_code = NULL, otp_expires_at = NULL WHERE user_id = ?");
                $clear_otp->bind_param("i", $temp_user_id);
                $clear_otp->execute();
            }
            $stmt->close();

        } else if ($action === 'password_reset') {
            
            $sql = "SELECT id FROM password_resets WHERE user_id = ? AND otp_code = ? AND expires_at > NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $temp_user_id, $entered_pin);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $is_valid = true;
                // Delete the reset token completely so it can't be reused
                $clear_otp = $conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $clear_otp->bind_param("i", $temp_user_id);
                $clear_otp->execute();
            }
            $stmt->close();
        }

        // -----------------------------------------------------
        // 2. THE ROUTER: Where do they go next?
        // -----------------------------------------------------
        if ($is_valid) {
            
            if ($action === '2fa_login') {
                // ACTION A: Log them into the system
                $user_sql = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $user_sql->bind_param("i", $temp_user_id);
                $user_sql->execute();
                $user_data = $user_sql->get_result()->fetch_assoc();
                $user_sql->close();

                session_regenerate_id(true);
                $_SESSION['user_id'] = $user_data['user_id'];
                $_SESSION['username'] = $user_data['username'];
                $_SESSION['fullname'] = $user_data['fullname'];
                $_SESSION['role'] = $user_data['role'];
                $_SESSION['branch_id'] = $user_data['branch_id'];
                $_SESSION['email'] = $user_data['email'];
                $_SESSION['status'] = $user_data['status'];
                $_SESSION['created_at'] = $user_data['created_at'];
                $_SESSION['is_readonly'] = $user['is_readonly'];
                
                unset($_SESSION['temp_user_id'], $_SESSION['temp_email'], $_SESSION['otp_action']);

                header("Location: ../dashboard/home.php");
                exit();

            } else if ($action === 'password_reset') {
                // ACTION B: Send them to reset their password
                $_SESSION['can_reset_password'] = true; 
                header("Location: change_password.php"); 
                exit();
            }

        } else {
            // Code was wrong or expired
            $error_message = "Invalid or expired OTP. Please try again.";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/auth/non-login.css">
    <!-- <link rel="stylesheet" href="../resources/css/pages/auth/login.css"> -->
</head>
<body>
    <?php
        if ($_SESSION['otp_action'] === '2fa_login')
        {
            echo '<button onclick="window.location.href=\'login.php\'" id="return"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>';
        } else {
            echo '<button onclick="window.location.href=\'forget_password.php\'" id="return"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>';
        }
    ?>
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
                    <h1>Verify One-Time Pin</h1>
                    <p>
                        <?php 
                            if ($_SESSION['otp_action'] === '2fa_login') {
                                echo "Check your email and verify your OTP to securely log in.";
                            } else if ($_SESSION['otp_action'] === 'password_reset') {
                                echo "Check your email and verify your OTP to change your password.";
                            } else {
                                echo "";
                            }
                        ?>
                    </p>
                </div>
                <form action="" method="POST">
                    <div class="email_input">   
                        <div class="input_container">
                            <input type="text" name="pin" id="pin" inputmode="numeric" autocomplete="one-time-code" pattern="\d{6}" maxlength="6" required>
                            <img src="../resources/img/icons/pin.png" alt="pin" class="left_icon1">
                            <img src="../resources/img/icons/pin_c.png" alt="pin" class="left_icon2">
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
                        <button type="submit" name="send">Verify OTP</button>
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