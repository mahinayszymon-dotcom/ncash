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
            $u_user_id = htmlspecialchars($_GET['id']);
        }
        else 
        {
            header("Location: ../auth/denied.php");
            exit();
        }

        $sql = "SELECT u.user_id, u.username, u.fullname, u.email, u.role, u.branch_id, b.branch_name, u.created_at, u.status 
                FROM users AS u
                LEFT JOIN branches AS b ON u.branch_id = b.branch_id
                WHERE u.user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $u_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $u_id = htmlspecialchars($row['user_id']);
            $u_uname    = htmlspecialchars($row['username']);
            $u_fullname = htmlspecialchars($row['fullname']);
            $u_email    = htmlspecialchars($row['email']);
            $u_role     = htmlspecialchars($row['role']);
            $u_branch   = htmlspecialchars($row['branch_id']);
            $u_status   = htmlspecialchars($row['status']); 
            $u_created_date  = new DateTime($row['created_at']);
            $formatted_date = $u_created_date->format("F j, Y");
        } else {
            // error
        }
        
        echo "<title>$u_fullname's Details</title>"
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
                        <h1>Staff Details</h1>
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
            <?php 
                if ($u_id == $user_id) {
                    echo '<div class="central_panelC">
                            <div class="data_tableC">
                                <span class="message_info"><img src="../../resources/img/icons/info.png" alt="info">You cannot change your own account details here, please access your Settings Page to edit your information.</span>
                            </div>
                        </div>'; 
                } else if ($u_id == 1) {
                    header("Location: ../../auth/denied.php");
                    exit();
                } else {
                    include('../../includes/staff_details_cont.php');
                }
            ?>
        </section>
    </main>
    <div id="overlay" class="overlay_hidden"></div>
    <div id="popup" class="popup_hidden">
        <div class="popup_content">
            <div class="popup_top">
                <h2>Reason for archiving</h2>
                <button id="closePopup"><img src="../../resources/img/icons/close.png" alt="close"></button>
            </div>
            <hr>
            <div class="popup_main">
                <div class="card_form">
                    <p>Please select a reason for archiving this item.</p>
                    <form action="" method="POST">
                        <table>
                            <tr>
                                <td><input type="radio" name="reason" value="Incorrect Details"></td>
                                <td>Incorrect Details</td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="reason" value="Data Duplication Entry"></td>
                                <td>Data Duplication Entry</td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="reason" value="Subject for Deletion"></td>
                                <td>Subject for Deletion</td>
                            </tr>
                            <tr>
                                <td><input type="radio" name="reason" value="Historical Review Complete"></td>
                                <td>Historical Review Complete</td>
                            </tr>
                            <tr>
                                <td colspan="2"><textarea style="resize: none; font-size: 15px;" name="custom_reason" rows="4" cols="50" placeholder="If cases above don't apply, type reason here."></textarea></td>
                            </tr>
                        </table>
                        <div class="modal-actions">
                            <button type="submit" id="proceed" name="proceed" class="btn-proceed"><img src="../../resources/img/icons/arrow_circle_right.png" alt="proceed">Proceed to Archive</button>
                        </div>
                    </form>
                </div>
                
            </div>        
        </div>
    </div>
    <script src="../../resources/js/event_buttonEdit.js"></script>
    <script>
        /*dat iload muna lahat ng page ung needed elements bago maclick eto*/
        document.addEventListener("DOMContentLoaded", function() {
            /*hanapin nya muna si button sa .archive_btn_cont*/
            /*hanapin din si overlay tas popup then yung close button*/
            const openBtn = document.querySelector(".details_info .archive_btn_cont button");
            const overlay = document.getElementById("overlay");
            const popup = document.getElementById("popup");
            const closeBtn = document.getElementById("closePopup");
            const proceedBtn = document.getElementById("proceed"); 

            /*clinick ko na si button, mag rurun eto. mostly css na maninipulate dito */
            openBtn.addEventListener("click", function(e) {
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
</body>
</html>