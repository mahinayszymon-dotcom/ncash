<?php
session_start();
include("../config/db_conn.php");

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header("Location: ../dashboard/home.php"); 
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$error_message = ""; // eto yung para sa error message sa baba

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['animation_played'] = true;

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Notice the LEFT JOIN here! We grab the 2FA status right away.
    $sql = "SELECT u.*, t.is_enabled 
            FROM users u 
            LEFT JOIN user_two_factor t ON u.user_id = t.user_id 
            WHERE u.username = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['register_status'] == 0) {
            $_SESSION['register_id'] = $user['user_id'];
            header("Location: register.php");
            exit();
        }

        if (password_verify($password, $user['password'])) {
            
            // STRICT CHECK: Is 2FA enabled?
            // Since we used LEFT JOIN, if the user isn't in the 2FA table yet, is_enabled will be NULL.
            // We strictly check if it equals 1.
            if (isset($user['is_enabled']) && $user['is_enabled'] == 1) {
                
                // 1. Generate a secure 6-digit OTP
                $otp = (string) random_int(100000, 999999);

                // 2. Save it to the database using MySQL's DATE_ADD function
                $update_otp = $conn->prepare("UPDATE user_two_factor SET otp_code = ?, otp_expires_at = DATE_ADD(NOW(), INTERVAL 10 MINUTE) WHERE user_id = ?");
                // Note: We changed "ssi" to "si" because we are only passing a String (OTP) and an Integer (User ID) now!
                $update_otp->bind_param("si", $otp, $user['user_id']);
                $update_otp->execute();
                $update_otp->close();

                // 3. Send the email
                require_once("../resources/external/phpmailer/mail_helper.php"); 
                
                $subject = "Your TraceMo Login Code";
                $message = "<h2>Your verification code is: <span style='color: #d93025;'>$otp</span></h2><p>This code will expire in 10 minutes. Do not share it with anyone.</p>";
                sendBusinessEmail($user['email'], $subject, $message);

                // 4. Set TEMPORARY sessions (Do not log them in!)
                $_SESSION['temp_user_id'] = $user['user_id'];
                $_SESSION['temp_email'] = $user['email'];
                $_SESSION['otp_action'] = '2fa_login';

                // 5. Send them to the OTP page
                header("Location: verify_otp.php");
                exit();
                
            } else {
                // --- NORMAL LOGIN FLOW (If 2FA is Disabled or NULL) ---
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['branch_id'] = $user['branch_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['status'] = $user['status'];
                $_SESSION['register_status'] = $user['register_status'];
                $_SESSION['created_at'] = $user['created_at'];

                if ($user['register_status'] == 1) {
                    header("Location: register.php");
                    exit();
                } else if ($user['register_status'] == 0) {
                    header("Location: ../dashboard/home.php");
                    exit();
                } else {
                    header("Location: ../auth/denied.php");
                    exit();
                }
            }

        } else {
            $error_message = "Invalid password. Try again.";
        }
    } else {
        $error_message = "Username not found or inactive.";
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link rel="icon" type="image/png" href="../resources/img/favicon.png">
        <link rel="stylesheet" href="../resources/css/base.css">
        <link rel="stylesheet" href="../resources/css/colors.css">
        <link rel="stylesheet" href="../resources/css/fonts.css">
        <link rel="stylesheet" href="../resources/css/pages/auth/login.css">
        <?php 
            if (!isset($_SESSION['animation_played'])) {
                echo '<link rel="stylesheet" href="../resources/css/pages/auth/animation.css">';
            }
        ?>
    </head>
    <script>
        window.addEventListener("pageshow", function (event) {
        if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
            window.location.reload();
        }
        });
    </script>

    <body>
        <section class="main">
            <div class="login_panel">
                <div class="login_panel_container">
                    <div class="logo">
                        <img src="../resources/img/ncash_logo_login2.png" alt="N-Cash Logo">
                        <span><strong>X</strong></span>
                        <img src="../resources/img/tracemo_logo.png" alt="Tracemo Logo">
                    </div>
                    <div class="login_header">
                        <h1>Log in to your Account</h1>
                        <p>Hello there! Please input your account information to log in.</p>
                    </div>
                    <form action="" method="POST">
                        <div class="login_input">
                            
                                <div class="input_container">
                                    <input type="text" name="username" id="username" placeholder="Username" autocomplete="off" required>
                                    <img src="../resources/img/icons/account.png" alt="username" class="left_icon1">
                                    <img src="../resources/img/icons/account_c.png" alt="usernamec" class="left_icon2">
                                </div>
                                <div class="input_container">
                                    <input type="password" name="password" id="password" placeholder="Password" onpaste="return false;" autocomplete="off" required>
                                    <img src="../resources/img/icons/password.png" alt="password" class="left_icon3">
                                    <img src="../resources/img/icons/password_c.png" alt="passwordc" class="left_icon4">
                                    <img src="../resources/img/icons/visibility_off.png" alt="password" class="visibility_icon">
                                    <img src="../resources/img/icons/visibility_off_c.png" alt="password" class="visibility_icon2">
                                    <!--
                                    <img src="../resources/img/icons/visibility_on.png" alt="password" class="visibility_icon3">
                                    <img src="../resources/img/icons/visibility_on_c.png" alt="password" class="visibility_icon4">
                                    -->
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
                                <div class="login_subcontent">
                                    <div class="subcontent_left">
                                        <input type="checkbox" id="rem" name="remember" value="remember">
                                        <label for="rem">Remember me</label>
                                    </div>
                                    <div class="subcontent_right">
                                        <!--NOTE: This directs to homepage. System not complete-->
                                        <a href="forget_password.php">Forgot password?</a>
                                    </div>                
                                </div>                            
                        </div>
                        <div class="login_btn_cont">
                            <button type="submit" name="login">Log in</button>
                        </div>
                    </form>
                    <div class="login_footer">
                        <p>© 2026 TraceMo · N-Cash Luxury Pawnshop | Developed by C&C</p>
                    </div>
                </div>
            </div>
            <div class="login_graphic">
                <div class="graphic_img">
                    <img src="../resources/img/icons/sample.png" alt="sample">
                </div>
                <div class="graphic_text">
                    <h2>Track, Manage, and Notify with TraceMo</h2>
                    <p>Everything your business needs for accurate records and timely client updates.</p>
                </div>
            </div>
        </section>
        <div class="result_cont_bar">
            <?php
                //$_SESSION['archive_success_msg'] = 'Test';

                if (isset($_SESSION['register_success_msg'])) {
                    echo "<span id=\"login_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['register_success_msg'] . "</span>";
        
                    echo "
                    <script>
                        // Function to hide the element
                        function hideMessage() {
                            var element = document.getElementById('login_success');
                            if (element) {
                                // Use CSS opacity/transition for a smooth fade out (optional)
                                element.style.transition = 'opacity 0.5s ease-out';
                                element.style.opacity = '0';

                                // Remove the element completely after the fade out is complete
                                setTimeout(function() {
                                    element.style.display = 'none';
                                    // Or remove it from the DOM entirely:
                                    // element.parentNode.removeChild(element);
                                }, 500); // 500ms should match your CSS transition time if you add one
                            }
                        }

                        // Call the hideMessage function after 3000 milliseconds (3 seconds)
                        setTimeout(hideMessage, 3000);
                    </script>
                    ";

                    unset($_SESSION['register_success_msg']);
                }
                else {
                    unset($_SESSION['register_success_msg']);
                }
            ?>
        </div>
    </body>
</html>
<script>
    const passwordInput = document.getElementById("password");
    const iconGray = document.querySelector(".visibility_icon");
    const iconColor = document.querySelector(".visibility_icon2");

    function toggleVisibility() {
        const isPassword = passwordInput.type === "password";

        if (isPassword) {
            passwordInput.type = "text";
            iconGray.src = "../resources/img/icons/visibility_on.png";
            iconColor.src = "../resources/img/icons/visibility_on_c.png";
        } else {
            passwordInput.type = "password";
            iconGray.src = "../resources/img/icons/visibility_off.png";
            iconColor.src = "../resources/img/icons/visibility_off_c.png";
        }
    }

    iconGray.addEventListener("click", toggleVisibility);
    iconColor.addEventListener("click", toggleVisibility);
</script>