<?php
    session_start();
    include("../config/db_conn.php");

    if (isset($_SESSION['user_id']) && isset($_SESSION['role']))
    {
        header("Location: ../dashboard/");
        exit();
    } else if (isset($_SESSION['register_status']) == 1)
    {
        header("Location: ../auth/denied.php");
        exit();
    } else {
        // unset($_SESSION['temp_verify']);
    }


    if(isset($_POST['send']))
    {
        $new_pass = htmlspecialchars($_POST['new_password']);
        $confirm_pass = htmlspecialchars($_POST['confirm_new_password']);

        if($new_pass === $confirm_pass)
        {
            $register_id = $_SESSION['register_id'];
            $hashedNewPass = password_hash($new_pass, PASSWORD_DEFAULT);
            $register_status = 1;

            $sql = "UPDATE users
                    SET password = ?, register_status = ?
                    WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sii", $hashedNewPass, $register_status, $register_id);
            if($stmt->execute())
            {
                $_SESSION['register_success_msg'] = "Registration Successful!";

                header("Location: ../auth/login.php");
                exit();
                unset($_SESSION['register_id']);
            }
        }
        else 
        {
            $error_message = "Password and Confirm Password doesn't match.";

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
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
    <button onclick="window.location.href='login.php'" id="return"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
    <section class="main2">
        <div class="forget_pass_graphic">
            <div class="graphic_img">
                <img src="../resources/img/icons/sample.png" alt="sample">
            </div>
            <div class="graphic_text">
                <h2>Welcome to TraceMo</h2>
                <p>Let's finalize your account first.</p>
            </div>
        </div>
        <div class="forget_pass_panel">
            <div class="forget_pass_panel_container">
                <div class="logo">
                    <img src="../resources/img/ncash_logo_login.png" alt="N-Cash Logo">
                </div>
                <div class="login_header">
                    <h1>Register Account</h1>
                    <p>Finalize your account by setting a new secure password.</p>
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
                            <input type="password" name="new_password" id="new_password" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{12,}" required>
                            <img src="../resources/img/icons/password.png" alt="pin" class="left_icon1">
                            <img src="../resources/img/icons/password_c.png" alt="pin" class="left_icon2">
                        </div>
                        <div class="input_container">
                            <label for="confirm_new_password">Confirm New Password</label>
                        </div>  
                        <div class="input_container">
                            <input type="password" name="confirm_new_password" id="confirm_new_password" autocomplete="off" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{12,}" required>
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
                <?php 
                    
                ?>
                <div class="forget_pass_footer">
                    <p>© 2026 TraceMo · N-Cash Luxury Pawnshop | Developed by C&C</p>
                </div>
            </div>
        </div>
    </section>
</body>
</html>