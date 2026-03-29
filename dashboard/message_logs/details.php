<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
include("../../db/branch_fetch.php");

if ($role !== "admin") {
    header("Location: ../../auth/denied.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        date_default_timezone_set('Asia/Manila');
        if(isset($_GET['id']))
        {
            $d_notif_id = htmlspecialchars($_GET['id']);  
        }
        else 
        {
            header("Location: ../../auth/denied.php");
            exit();
        }

        $sql = "SELECT n.notif_id, n.branch_id, c.fullname, n.message, n.type, n.status, n.date_sent
                FROM notifs AS n
                INNER JOIN clients AS c ON n.client_id = c.client_id
                WHERE n.notif_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $d_notif_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();

            $m_notif_id = htmlspecialchars($row['notif_id']);
            $m_branch_id = htmlspecialchars($row['branch_id']);
            $m_client_name = htmlspecialchars($row['fullname']);
            $m_message = htmlspecialchars($row['message']);
            $m_type = htmlspecialchars($row['type']);
            $m_status = htmlspecialchars($row['status']);
            $m_date_sent = htmlspecialchars($row['date_sent']);

            $format_date = date("j M Y", strtotime($m_date_sent));

            $audit_u_id = $_SESSION['user_id'];
            $audit_action = "Accessed";
            $audit_obj = "Notif";
            $audit_desc = "Accessed notification with notif id: $m_notif_id";

            $curDate = new DateTime();
            $current = $curDate->format('Y-m-d H:i:s');

            $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $m_branch_id, $current);
            $stmt->execute();
        } else {
            header("Location: ../../404.php");
            exit();
        }
        
        echo "<title>Message Log $m_notif_id</title>"
    ?>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/staff_management.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/details/details.css">
</head>
<body>
    <main class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <div class="top_panel">
                <div class="top_panel_content">
                    <div class="text_cont">
                        <h1>Message Log</h1>
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
            <div class="central_panelF">
                <div class="data_controlsC">
                    <div class="data_controls_header">
                        <div class="data_controls_header_text">
                            <div class="icon_normal">
                                <img src="../../resources/img/icons/description_w.png" alt="description">
                            </div>
                            <?php 
                                echo "<h2>Log Information of $m_notif_id</h2>";
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../message_logs.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="row_cont">
                                <?php
                                    $status_style = "font-size: 15px;";
                                    
                                    if ($m_status == 'Sent') {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400; padding: 5px 8px; border-radius: 5px; background-color: #d2e8ce; color: #739667;";
                                    } 
                                    else if ($m_status == 'Failed') {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #e9cbcb; color: #915656;";
                                    } else {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: var(--main-background-data) !important; color: var(--loading-dark);";
                                    }

                                    echo '<span style="' . $status_style . '">' . $m_status . '</span>';
                                ?>
                            </div>
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created at: <?php echo $format_date ?> </span>
                                </div>
                            </div>
                            <hr>
                            <?php  
                                if($is_readonly)
                                {
                                    echo "<div class=\"disable_btn_cont\">
                                              <button type=\"submit\" name=\"submit\"><img src=\"../../resources/img/icons/delete_forever_w.png\" alt=\"delete\">Delete Log</button>
                                          </div>";
                                }
                            ?>
                        </div>
                            
                        <div class="details_editable">
                            <div class="email-container">    
                                <div class="email-header">
                                    <div class="header-row">
                                        <span class="label">Date:</span><?= $format_date; ?>
                                    </div>
                                    <div class="header-row">
                                        <span class="label">To:</span><?= $m_client_name . " (" . $m_type . ")"; ?>
                                    </div>
                                    <div class="header-row">
                                        <span class="label">Subject:</span><strong>Unknown Subject</strong>
                                    </div>
                                </div>
                                <div class="email-body">
                                    <?= $m_message; ?>
                                </div>
                            </div>
                            <div class="result_cont">
                                <?php
                                    // $_SESSION['change_success_msg'] = '';

                                    if (isset($_SESSION['change_success_msg'])) {
                                        $redirect_url = "../../dashboard/inventory.php";
                                        $delay = 3; // three seconds muna taymperst

                                        echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
                                        
                                        echo "<meta http-equiv='refresh' content='" . $delay . "; url=" . $redirect_url . "'>";

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
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>