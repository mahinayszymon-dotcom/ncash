<?php
include("../config/session_check.php");
include("../config/db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/settings.css">
    <style>
        .settings_navigation .settings_nav_links ul a:nth-child(1) li {
            border-right: 5px solid var(--red-light);
        }
    </style>
</head>
<body>
    <section class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <?php
                include('../includes/top_panel.php')
            ?>
            <div class="settings_central_panel">
                <div class="settings_container">
                    <div class="settings_navigation">
                        <div class="settings_help_info">
                            <div class="info_in_settings">
                                <?php
                                    $current_page = $_SERVER['PHP_SELF'];

                                    if ($current_page === BASE_URL . "dashboard/settings/account.php" || $current_page === BASE_URL . "dashboard/settings/activity_logs.php" || $current_page === BASE_URL . "dashboard/settings/preferences.php" || $current_page === BASE_URL . "dashboard/settings/security.php" || $current_page === BASE_URL . "dashboard/settings/system.php") { 
                                        echo "<span class=\"message_info\"><img src=\"../../resources/img/icons/bulb.png\" alt=\"info\">" . "You’re in control — adjust your settings any way you like." . "</span>";
                                    } else {
                                        echo "<span class=\"message_info\"><img src=\"../resources/img/icons/bulb.png\" alt=\"info\">" . "You’re in control — adjust your settings any way you like." . "</span>";
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="settings_nav_links">
                            <ul>
                                <a href="../dashboard/settings.php"><li><img src="../resources/img/icons/settings_small.png" alt="general">General</li></a>
                                <a href="../dashboard/settings/account.php"><li><img src="../resources/img/icons/user.png" alt="account">Account</li></a>
                                <a href="../dashboard/settings/security.php"><li><img src="../resources/img/icons/security.png" alt="security">Security</li></a>
                                <a href="../dashboard/settings/activity_logs.php"><li><img src="../resources/img/icons/logs.png" alt="activity_logs">Activity Logs</li></a>
                                <?php 
                                    if ($role == "admin") {
                                        echo "<a href=\"../dashboard/settings/system.php\"><li><img src=\"../resources/img/icons/website.png\" alt=\"system\">System</li></a>";
                                    }
                                ?>
                            </ul>
                        </div>
                    </div>
                    <div class="settings_main">
                        <div class="settings_main_top_panel">
                            <h1>General</h1>
                            <p>Basic information and overall settings that apply to your profile or workspace.</p>
                        </div>
                        <hr>
                        <div class="settings_main_central_panel">
                            <div class="card_general">
                                <div class="card_general_profile">
                                    <div class="profile_circle">
                                        <?php
                                            echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                                        ?>
                                    </div>
                                    <div class="profile_text">
                                        <?php
                                            echo "<p>" . ucwords($fullname) . "</p>";
                                            echo "<p class=\"p_settings\">" . ucwords($branch_name) . " (" . ucwords($role) . ")</p>";
                                        ?>
                                    </div>
                                    <div class="profile_more">
                                        <button type="submit"><img src="../resources/img/icons/open.png" alt="open"></button>
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="card_general" style="cursor: pointer;" onclick="window.location.href='../dashboard/userhelp.php'">
                                <div class="card_general_profile">
                                    <div class="profile_text">
                                        <p>Help Center</p>
                                        <p class="p_settings">Access Help Center to view articles about the system</p>
                                    </div>
                                    <div class="profile_more">
                                        <button onclick="window.location.href='../dashboard/userhelp.php'"><img src="../resources/img/icons/chevron_right.png" alt="chevron_right"></button>
                                    </div>
                                </div>
                            </div> -->
                            <!-- <div class="card_general" style="cursor: pointer;" onclick="window.location.href='../archives/archived_items.php'">
                                <div class="card_general_profile">
                                    <div class="profile_text">
                                        <p>Visit Archive</p>
                                        <p class="p_settings">Access the Archive page to view redeemed items and past records.</p>
                                    </div>
                                    <div class="profile_more">
                                        <button onclick="window.location.href='../dashboard/userhelp.php'"><img src="../resources/img/icons/chevron_right.png" alt="chevron_right"></button>
                                    </div>
                                </div>
                            </div> -->
                            <!-- <div class="card_general" style="cursor: pointer;" onclick="window.open('../resources/files/Manual(Test).pdf')">
                                <div class="card_general_profile">
                                    <div class="profile_text">
                                        <p>User Guide</p>
                                        <p class="p_settings">View or download this system's user manual.</p>
                                    </div>
                                    <div class="profile_more">
                                        <button onclick="window.open('../resources/files/Manual(Test).pdf')"><img src="../resources/img/icons/download.png" alt="download"></button>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <div id="overlay" class="overlay_hidden"></div>
    <div id="popup" class="popup_hidden">
        <div class="popup_content">
            <div class="popup_top">
                <button id="closePopup"><img src="../resources/img/icons/close.png" alt="close"></button>
            </div>
            <div class="popup_main">
                <div class="card_general_profile">
                    <div class="profile_circle">
                        <?php
                            echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                        ?>
                    </div>
                    <div class="profile_text">
                        <?php
                            echo "<p>" . ucwords($fullname) . "</p>";
                        ?>
                    </div>
                </div>
                <br>
                <hr>
                <div class="card_more_info">
                    <?php
                        echo "<p class=\"p_settings\">Username:</p>";
                        echo "<p class=\"p_settings\">" . $_SESSION['username'] . "</p>";
                        echo "<p class=\"p_settings\">Branch:</p>";
                        echo "<p class=\"p_settings\">" . ucwords($branch_name) . "</p>";
                        echo "<p class=\"p_settings\">Email:</p>";
                        echo "<p class=\"p_settings\">" . $email. "</p>";
                        echo "<p class=\"p_settings\">Role:</p>";
                        echo "<p class=\"p_settings\">" . $role. "</p>";
                        echo "<p class=\"p_settings\">Date Created:</p>";
                        echo "<p class=\"p_settings\">" . $created_at_date . "</p>";
                    ?>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
<script>
    /*dat iload muna lahat ng page ung needed elements bago maclick eto*/
    document.addEventListener("DOMContentLoaded", function() {
        /*hanapin nya muna si button sa .profile_more*/
        /*hanapin din si overlay tas popup then yung close button*/
        const openBtn = document.querySelector(".card_general_profile .profile_more button");
        const overlay = document.getElementById("overlay");
        const popup = document.getElementById("popup");
        const closeBtn = document.getElementById("closePopup");

        /*clinick ko na si button, mag rurun eto. mostly css na maninipulate dito */
        openBtn.addEventListener("click", function(e) {
            e.preventDefault(); /*nilagay ko to kasi nagrerequest sya ng form resubmission*/
            overlay.style.display = "block";
            popup.style.display = "block";
        });

        closeBtn.addEventListener("click", function() {
            overlay.style.display = "none";
            popup.style.display = "none";
        });

        overlay.addEventListener("click", function() {
            overlay.style.display = "none";
            popup.style.display = "none";
        });
    });
</script>
