<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/settings.css">
    <style>
        .settings_navigation .settings_nav_links ul a:nth-child(3) li {
            border-right: 5px solid var(--red-light);
        }
    </style>
</head>
<body>
    <section class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <div class="top_panel">
                <div class="top_panel_content">
                    <div class="text_cont">
                        <h1>Settings</h1>
                    </div>
                    <div class="search_cont">
                        <input type="text" placeholder="<?php echo "What would you like to search this " . date('l') . "?";?>">
                        <img src="../../resources/img/icons/search.png" alt="search">
                    </div>
                    <div class="account_cont">       
                        <div class="profile_circle">
                            <?php
                                echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                            ?>
                        </div>
                        <div class="profile_text">
                            <?php
                                echo "<a href=\"../../dashboard/settings.php\"><p>" . ucwords($fullname) . "</p></a>";
                                echo "<p>" . ucwords($branch_name) . " (" . ucwords($role) . ")</p>";
                            ?>
                        </div>
                        <div class="account_cont_actions"> 
                            <button onclick="window.location.href='../../dashboard/notifications.php';"><img src="../../resources/img/icons/notif.png" alt="notifications"></button>       
                            <form id="logout-form" action="../../auth/logout.php" method="POST">
                                <button type="submit"><img src="../../resources/img/icons/logout.png" alt="logout"></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="settings_central_panel">
                <div class="settings_container">
                    <div class="settings_navigation">
                        <div class="settings_help_info">
                            <div class="info_in_settings">
                                <?php
                                    $current_page = $_SERVER['PHP_SELF'];

                                    if ($current_page === "/ncash-tracemo/dashboard/settings/account.php" || $current_page === "/ncash-tracemo/dashboard/settings/activity_logs.php" || $current_page === "/ncash-tracemo/dashboard/settings/preferences.php" || $current_page === "/ncash-tracemo/dashboard/settings/security.php" || $current_page === "/ncash-tracemo/dashboard/settings/system.php") { 
                                        echo "<span class=\"message_info\"><img src=\"../../resources/img/icons/bulb.png\" alt=\"info\">" . "You’re in control — adjust your settings any way you like." . "</span>";
                                    } else {
                                        echo "<span class=\"message_info\"><img src=\"../resources/img/icons/bulb.png\" alt=\"info\">" . "You’re in control — adjust your settings any way you like." . "</span>";
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="settings_nav_links">
                            <ul>
                                <a href="../../dashboard/settings.php"><li><img src="../../resources/img/icons/settings_small.png" alt="general">General</li></a>
                                <a href="../../dashboard/settings/account.php"><li><img src="../../resources/img/icons/user.png" alt="account">Account</li></a>
                                <a href="../../dashboard/settings/security.php"><li><img src="../../resources/img/icons/security.png" alt="security">Security</li></a>
                                <a href="../../dashboard/settings/activity_logs.php"><li><img src="../../resources/img/icons/logs.png" alt="activity_logs">Activity Logs</li></a>
                                <?php 
                                    if ($role == "admin") {
                                        echo "<a href=\"../../dashboard/settings/system.php\"><li><img src=\"../../resources/img/icons/website.png\" alt=\"system\">System</li></a>";
                                    }
                                ?>
                            </ul>
                        </div>
                    </div>
                    <div class="settings_main">
                        <div class="settings_main_top_panel">
                            <h1>Security</h1>
                            <p>Protect your account with advanced security preferences and access controls.</p>
                        </div>
                        <hr>
                        <div class="settings_main_central_panel">
                            <div class="card_general">
                                <?php include("../../includes/change_pass_form.php") ?> 
                            </div>
                            <?php
                                $user_id = $_SESSION['user_id']; 

                                // Fetch user's email from the main users table
                                $stmt1 = $conn->prepare("SELECT username FROM users WHERE user_id = ?");
                                $stmt1->bind_param("i", $user_id);
                                $stmt1->execute();
                                $result1 = $stmt1->get_result();
                                $userData = $result1->fetch_assoc();

                                // ALWAYS CLOSE the previous statement before opening a new one!
                                $stmt1->close(); 

                                $stmt2fa = $conn->prepare("SELECT is_enabled FROM user_two_factor WHERE user_id = ?");

                                // THE FIX: Bind the $user_id to the '?' in the query above
                                $stmt2fa->bind_param("i", $user_id);

                                // 1. Execute the query
                                $stmt2fa->execute();

                                // 2. Get the result set
                                $result2fa = $stmt2fa->get_result();

                                // 3. FETCH the actual data as an associative array
                                $tfaData = $result2fa->fetch_assoc(); 

                                // 4. Now your original line will work perfectly!
                                $is_tfa_enabled = ($tfaData && $tfaData['is_enabled'] == 1);

                                // Close this statement too when you are done!
                                $stmt2fa->close();
                            ?>
                            <div class="card_general">
                                <div class="card_general_profile" class="card_main">
                                    <div class="profile_text">             
                                        <p>Two-Factor Authentication</p>
                                        <p class="p_settings">Add an extra layer of security to your account.</p>
                                    </div>          
                                    <div class="profile_more">
                                        <button onclick="this.closest('.card_general').querySelector('.dropdown_content').classList.toggle('show'); this.classList.toggle('rotate')">
                                            <img src="../../resources/img/icons/chevron_down.png" alt="chevron_down">
                                        </button>
                                    </div>
                                </div>
                                <div class="dropdown_content">
                                    <div class="tfa_container">
                                        <div class="tfa_content">
                                            <p>Two-factor authentication</p>
                                            <p>
                                                <?php if ($is_tfa_enabled): ?>
                                                    <span style="color: var(--success); font-size: 1rem;">Enabled</span>
                                                <?php else: ?>
                                                    <span style="color: var(--loading-dark); font-size: 1rem;">Disabled</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="tfa_content">
                                            <p>Two-factor authentication method</p>
                                            <p>
                                                <?php echo $is_tfa_enabled ? 'Email (' . htmlspecialchars($email) . ')' : 'No email selected'; ?>
                                            </p>
                                        </div>
                                        <div class="tfa_content_btn">
                                            <button onclick="openTfaModal()">Edit two-factor authentication settings</button>
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <div id="tfaModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" onclick="closeTfaModal()" style="cursor:pointer;">&times;</span>
            <h2>Two-Factor Authentication Settings</h2>
            
            <form action="../../includes/update_2fa.php" method="POST">
                <p>Receive a 6-digit code via email every time you log in.</p>
                
                <div class="radio-group">
                    <label for="tfa_enable">
                        <input type="radio" id="tfa_enable" name="tfa_status" value="1" <?php echo $is_tfa_enabled ? 'checked' : ''; ?>> 
                        <span>Enable Email 2FA</span>
                    </label>
                    
                    <label for="tfa_disable">
                        <input type="radio" id="tfa_disable" name="tfa_status" value="0" <?php echo !$is_tfa_enabled ? 'checked' : ''; ?>> 
                        <span>Disable 2FA</span>
                    </label>
                </div>
                
                <button type="submit" class="button_save">Save Changes</button>
            </form>
        </div>
    </div>
    <script>
        function openTfaModal() { document.getElementById('tfaModal').style.display = 'block'; }
        function closeTfaModal() { document.getElementById('tfaModal').style.display = 'none'; }
        window.onclick = function(event) {
            const modal = document.getElementById('tfaModal');
            // Check if what they clicked was the dark background (the modal wrapper itself)
            if (event.target === modal) {
                closeTfaModal();
            }
        }
    </script>
</body>
</html>
<script>
    const passwordInput = document.getElementById("password");
    const visibilityOff = document.querySelector(".visibility_icon");
    const visibilityOffC = document.querySelector(".visibility_icon2");

    // Only add the event listener if the icon actually exists on the page
    if (visibilityOff && passwordInput) {
        visibilityOff.addEventListener("click", () => {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                visibilityOff.src = "../../resources/img/icons/visibility_on.png";
            } else {
                passwordInput.type = "password";
                visibilityOff.src = "../../resources/img/icons/visibility_off.png";
            }
        });
    }

    // Only add the event listener if the second icon actually exists
    if (visibilityOffC && passwordInput) {
        visibilityOffC.addEventListener("click", () => {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                visibilityOffC.src = "../../resources/img/icons/visibility_on_c.png";
            } else {
                passwordInput.type = "password";
                visibilityOffC.src = "../../resources/img/icons/visibility_off_c.png";
            }
        });
    }
</script>