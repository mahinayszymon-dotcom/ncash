<?php
ob_start();
include("../config/session_check.php");
include("../config/db_conn.php");
include("../db/branch_fetch.php");
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
            $client_id = htmlspecialchars($_GET['id']);
        }
        else 
        {
            header("Location: ../auth/denied.php");
            exit();
        }

        $sql = "SELECT ca.archived_by, ca.archived_date, ca.client_id, ca.fullname, ca.contact, ca.email, ca.address, ca.created_at, ca.reason, ca.delete_period
                FROM clients_archive AS ca
                WHERE ca.client_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $archiver = htmlspecialchars($row['archived_by']);
            $arch_date = htmlspecialchars($row['archived_date']);
            $a_client_name = htmlspecialchars($row['fullname']);
            $a_contact = htmlspecialchars($row['contact']);
            $a_email = htmlspecialchars($row['email']);
            $a_addr = htmlspecialchars($row['address']);
            $a_created_at = htmlspecialchars($row['created_at']);
            $a_reason = htmlspecialchars($row['reason']);

            if(is_null($row['delete_period']))
            {
                $deleted_at = "No deletion period set.";
            }
            else 
            {
                $delDate = new DateTime($row['delete_period']);
                $deleted_at = $delDate->format("F j, Y");
            }
        }

        $createDate = new DateTime($a_created_at);
        $created_at = $createDate->format("F j, Y");
        $archDate = new DateTime($arch_date);
        $archived_at = $archDate->format("F j, Y");

        

        if($archiver != "system")
        {
            $sql = "SELECT u.fullname AS archiver, u.username FROM clients_archive AS ca
                    INNER JOIN users AS u ON ca.archived_by = u.username
                    WHERE ca.client_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $client_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0)
            {
                $row = $result->fetch_assoc();
                $arch_name = htmlspecialchars($row['archiver']);
            }
        }
        else
        {
            $arch_name = "System";
        }

        echo "<title>$a_client_name's Details</title>"
    ?>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/inventory.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/details/details.css">
    <style>
        .nav_links ul li:nth-child(3) {
            background-color: transparent;
            opacity: 0.85;
        }

        .nav_links ul li:nth-child(3) img {
            opacity: 0.8;
        } 
    </style>
</head>
<body>
    <main class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <div class="top_panel">
                <div class="top_panel_content">
                    <div class="text_cont">
                        <h1>Client Details</h1>
                    </div>
                    <div class="search_cont">
                        <input type="text" placeholder="<?php echo "What would you like to search this " . date('l') . "?";?>">
                        <img src="../resources/img/icons/search.png" alt="search">
                    </div>
                    <div class="account_cont">       
                        <div class="profile_circle">
                            <?php
                                echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                            ?>
                        </div>
                        <div class="profile_text">
                            <?php
                                echo "<a href=\"../dashboard/settings.php\"><p>" . ucwords($fullname) . "</p></a>";
                                echo "<p>" . ucwords($branch_name) . " (" . ucwords($role) . ")</p>";
                            ?>
                        </div>
                        <div class="account_cont_actions"> 
                            <button onclick="window.location.href='../dashboard/notifications.php';"><img src="../resources/img/icons/notif.png" alt="notifications"></button>      
                            <form id="logout-form" action="../auth/logout.php" method="POST">
                                <button type="submit"><img src="../resources/img/icons/logout.png" alt="logout"></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="central_panelC">
                <div class="data_controlsB">
                    <div class="data_controls_header">
                        <div class="data_controls_header_text">
                            <div class="icon_normal">
                                <img src="../resources/img/icons/description_w.png" alt="description">
                            </div>
                            <?php 
                                echo "<h2>$a_client_name's Information</h2>"
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='archived_clients.php'"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created by: </span>
                                </div>
                                <div class="row_cont">
                                    <span>Created at: <?php echo $created_at; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Archived by: <?php echo $arch_name; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Archived at: <?php echo $archived_at; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Deletion period: <?php echo $deleted_at; ?> </span>
                                </div>
                            </div>
                            <hr>
                            <form action="" method="POST">
                                <div class="archive_btn_cont">
                                    <?php 
                                        //kapag gagawa ka ng parang cancel deletion
                                        // if(isset($_POST['name_na_usto_mo'])) <- note mo ung name neto
                                        // {
                                        //     $sql = "UPDATE clients_archive
                                        //             SET delete_period = NULL
                                        //             WHERE client_id = ?";
                                        //     $stmt->prepare($sql);
                                        //     $stmt->bind_param("i", $client_id);
                                        //     if($stmt->execute())
                                        //     {
                                        //         $_SESSION['period_success_msg'] = "Deletion period successfully cancelled.";

                                        //         header("Location: ../archives/archived_clients.php");
                                        //         exit(); 
                                        //     }
                                        // }

                                        if(isset($_POST['delete']))
                                        {
                                            $curDate = new DateTime();
                                            $del_period = $curDate->modify('+7days');
                                            $deleted = $del_period->format('Y-m-d H:i:s');
                                            $del_str = $del_period->format('F j, Y, g:i:s a');
                                            
                                            $sql = "UPDATE clients_archive
                                                    SET delete_period = ?
                                                    WHERE client_id = ?";
                                            $stmt->prepare($sql);
                                            $stmt->bind_param("si", $deleted, $client_id);
                                            if($stmt->execute())
                                            {
                                                $_SESSION['period_success_msg'] = "Client will be deleted at $del_str.";

                                                header("Location: ../archives/archived_clients.php");
                                                exit(); 
                                            }
                                        }

                                        if ($role === "admin")
                                        {
                                            echo '<br><hr>
                                                <button type="submit" name="delete" onclick="return confirm(\'This process cannot be undone. Are you sure you want to proceed?\');"><img src="../resources/img/icons/delete_forever_w.png" alt="delete">Delete Permanently</button>
                                                <div class="archive_text">
                                                    <span class="message_warning"><img src="../resources/img/icons/warning.png" alt="warning">Deleting this data cannot be undone. Once deleted, all items and transactions binded to this client would also be deleted.</span>
                                                </div>';
                                        }
                                        // dito mo lagay ung pang cancel or kung san mo man usto (basta name="name_na_usto_mo")
                                    ?>
                                    
                                </div>
                            </form>
                        </div>
                        <div class="details_editable">
                            <form action="" method="POST" class="editable_item_section">
                                <div class="item_info_detail_row">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" class="item_tags" value="<?php echo $a_client_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_email">Client Email</label>
                                    <input type="email" name="client_email" id="client_email" class="item_tags" value="<?php echo $a_email; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_contact">Client Contact Info</label>
                                    <input type="text" name="client_contact" id="client_contact" class="item_tags" value="<?php echo $a_contact; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="address">Client Address</label>
                                    <input type="text" name="address" id="address" class="item_tags" value="<?php echo $a_addr; ?>" disabled>
                                </div>
                            </form>
                            <div class="result_cont">
                                <?php
                                    // $_SESSION['change_success_msg'] = '';

                                    if (isset($_SESSION['change_success_msg'])) {
                                        echo "<span class=\"message_success\"><img src=\"../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
                                        unset($_SESSION['change_success_msg']);
                                    } else if (isset($_SESSION['error_msg'])) {
                                        echo "<span class=\"message_error\"><img src=\"../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['error_msg'] . "</span>";
                                        unset($_SESSION['error_msg']);
                                    } else if (isset($_SESSION['nochange_msg'])) {
                                        echo "<span class=\"message_info\"><img src=\"../resources/img/icons/info.png\" alt=\"info\">" . $_SESSION['nochange_msg'] . "</span>";
                                        unset($_SESSION['nochange_msg']);
                                    } else {
                                        unset($_SESSION['change_success_msg']);
                                        unset($_SESSION['nochange_msg']);
                                    }
                                ?>
                            </div>
                        </div>
                        <?php
                            try
                            {
                                
                            }
                            catch (Throwable $e)
                            {
                                $_SESSION['error_msg'] = $e->getMessage();
                            }
                        ?>  
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="../resources/js/fetch_agreement.js"></script>
    <script src="../resources/js/event_buttonEdit.js"></script>
</body>
</html>