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
                            <form id="notif-form" action="" method="GET">
                                <button type="submit"><img src="../../resources/img/icons/notif.png" alt="notifications"></button>
                            </form>        
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
                                <a href="../../dashboard/settings/preferences.php"><li><img src="../../resources/img/icons/preferences.png" alt="preferences">Preferences</li></a>
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
                            <div class="card_general">
                                <div class="card_general_profile" class="card_main">
                                    <div class="profile_text">             
                                        <p>Two-Factor Authentication</p>
                                        <p class="p_settings">Add an extra layer of security to your account.</p>
                                    </div>          
                                    <div class="profile_more">
                                        <button onclick="this.closest('.card_general').querySelector('.dropdown_content').classList.toggle('show'); this.classList.toggle('rotate')"><img src="../../resources/img/icons/chevron_down.png" alt="chevron_down"></button>
                                    </div>
                                </div>
                                <div class="dropdown_content">
                                    <div class="tfa_container">
                                        <div class="tfa_content">
                                            <p>Two-factor authentication</p>
                                            <p>Disabled</p>
                                            <!--Output-->
                                        </div>
                                        <div class="tfa_content">
                                            <p>Two-factor authentication method</p>
                                            <p>None selected</p>
                                        </div>
                                        <div class="tfa_content_btn">
                                            <button>Edit two-factor authentication settings</button>
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
</body>
</html>
<script>
    const passwordInput = document.getElementById("password");
    const visibilityOff = document.querySelector(".visibility_icon");
    const visibilityOffC = document.querySelector(".visibility_icon2");

    visibilityOff.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            visibilityOff.src = "../../resources/img/icons/visibility_on.png";
        } else {
            passwordInput.type = "password";
            visibilityOff.src = "../../resources/img/icons/visibility_off.png";
        }
    });

    visibilityOffC.addEventListener("click", () => {
        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            visibilityOffC.src = "../../resources/img/icons/visibility_on_c.png";
        } else {
            passwordInput.type = "password";
            visibilityOffC.src = "../../resources/img/icons/visibility_off_c.png";
        }
    });
</script>