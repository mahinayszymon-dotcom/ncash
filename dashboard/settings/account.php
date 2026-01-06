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
        .settings_navigation .settings_nav_links ul a:nth-child(2) li {
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
                            <h1>Account</h1>
                            <p>Manage your login details and account credentials.</p>
                        </div>
                        <hr>
                        <div class="settings_main_central_panel">
                            <div class="card_general">
                                <form action="" method="POST" class="editable_profile_section">
                                    <div class="profile_text">             
                                        <p>Hello <?php echo $_SESSION['username'] . '!';?></p>
                                        <p class="p_settings">Below are your profile information.</p>
                                    </div> 
                                    <?php 
                                        if ($role !== "admin") {
                                            echo '<div class="account_info_detail_info">
                                                    <span class="message_info"><img src="../../resources/img/icons/bulb.png" alt="info">If you have more concerns about your account, contact your administrator.</span>
                                                </div>  ';
                                        }
                                    ?>
                                    <div class="account_info_detail_row">
                                        <label for="name">Your Name</label>
                                        <input type="text" name="fullname" id="fullname" class="profile_tags" value="<?php echo $fullname; ?>" disabled>
                                    </div>
                                    <div class="account_info_detail_row">
                                        <label for="name">Email</label>
                                        <input type="email" name="email" id="email" class="profile_tags" value="<?php echo $email; ?>" disabled>
                                    </div>
                                    <?php
                                        if ($user_id == 1) {
                                            echo '<div class="account_info_detail_btn">
                                                <span class="message_info"><img src="../../resources/img/icons/info.png" alt="info">The Primary Admin account is protected by system integrity rules. Credentials for this root user are permanent and cannot be modified within the management module.</span>
                                            </div>';
                                        } else {
                                            echo '<div class="account_info_detail_btn">
                                                    <button type="button">Edit</button>
                                                    <button type="submit" name="submit" disabled>Save Changes</button>
                                                </div>';
                                        }
                                    ?>
                                </form>
                                <div class="result_cont">
                                    <?php
                                        if (isset($_SESSION['change_success_msg'])) {
                                            echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
                                            unset($_SESSION['change_success_msg']);
                                        } else if (isset($_SESSION['error_msg'])) {
                                            echo "<span class=\"message_error\"><img src=\"../../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['error_msg'] . "</span>";
                                            unset($_SESSION['error_msg']);
                                        } else if (isset($_SESSION['nochange_msg'])) {
                                            echo "<span class=\"message_info\"><img src=\"../../resources/img/icons/info.png\" alt=\"info\">" . $_SESSION['nochange_msg'] . "</span>";
                                            unset($_SESSION['nochange_msg']);
                                        } else {
                                            unset($_SESSION['change_success_msg']);
                                            unset($_SESSION['nochange_msg']);
                                        }
                                    ?>
                                </div>
                                <?php 
                                    if(isset($_POST['submit']))
                                    {
                                        $sql = "SELECT fullname, email FROM users WHERE user_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("s", $user_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if($result->num_rows > 0)
                                        {
                                            $row = $result->fetch_assoc();
                                            $fullname = htmlspecialchars($row['fullname']);
                                            $email = htmlspecialchars($row['email']);
                                        }

                                        if(isset($fullname) && isset($email))
                                        {
                                            $newName = trim(htmlspecialchars($_POST['fullname']));
                                            $newEmail = trim(htmlspecialchars($_POST['email']));

                                            if(empty($newName) || empty($newEmail))
                                            {
                                                $_SESSION['error_msg'] = "Please fill out all input fields.";

                                                header("Location: " . $_SERVER['REQUEST_URI']);
                                                exit();
                                            }
                                            else 
                                            {
                                                if($fullname === $newName && $email === $newEmail)
                                                {
                                                    $_SESSION['nochange_msg']= "Account info remains the same. No changes has been done";

                                                    header("Location: " . $_SERVER['REQUEST_URI']);
                                                    exit();
                                                }
                                                else 
                                                {
                                                    $sql = "SELECT email FROM users WHERE user_id != ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $user_id);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();

                                                    if($result->num_rows > 0)
                                                    {
                                                        while($row = $result->fetch_assoc())
                                                        {
                                                            $existingEmail = htmlspecialchars($row['email']);
                                                            if($newEmail == $existingEmail)
                                                            {
                                                                $match = true;
                                                                break;
                                                            }
                                                        }

                                                        if(isset($match) && $match == true)
                                                        {
                                                            // echo "<p> Email already in use! </p>";
                                                            $_SESSION['error_msg'] = "Email already in use!";
                                                        }
                                                        else 
                                                        {
                                                            $sql = "UPDATE users
                                                                    SET fullname = ?, email = ?
                                                                    WHERE user_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("ssi", $newName, $newEmail, $user_id);
                                                            if($stmt->execute())
                                                            {
                                                                $_SESSION['fullname'] = $newName; 
                                                                $_SESSION['email'] = $newEmail;
                                                                
                                                                // echo "<p> Account Information Successfully Changed! </p>";
                                                                $_SESSION['change_success_msg'] = "Account Information Successfully Changed!";
                                                                sleep(1);
                                                                header('Location: ' . $_SERVER['PHP_SELF']);
                                                                exit();
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    ob_end_flush();
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <script src="../../resources/js/event_buttonEdit.js"></script>
</body>
</html>