<?php
    session_start();
    include("../config/db_conn.php");

    if (isset($_SESSION['user_id']) && isset($_SESSION['role']))
    {
        header("Location: ../dashboard/");
        exit();
    } else if (is_null($_SESSION['temp_email']))
    {
        header("Location: ../auth/denied.php");
        exit();
    } else {
        // BLANK
    }

    $_SESSION['temp_verify'] = "VER" // kapag may logic na yung button isama mo eto.
    
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
    <button onclick="window.location.href='forget_password.php'" id="return"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
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
                    <p>Check your email and verify your OTP to change your password.</p>
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