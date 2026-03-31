<?php
ob_start();
include("../config/session_check.php");
include("../config/db_conn.php");
include("../db/branch_fetch.php");

$is_readonly = $_SESSION['is_readonly'];
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
            $transaction_id = htmlspecialchars($_GET['id']);
        }
        else 
        {
            header("Location: ../auth/denied.php");
            exit();
        }

        $sql = "SELECT ta.archived_by, ta.archived_date, ta.agreement_num, COALESCE(c.fullname, ca.fullname) AS fullname, COALESCE(i.item_id, ia.item_id) AS item_id, COALESCE(i.item_name, ia.item_name) AS item_name, ta.branch_id, b.branch_name, ta.amount, ta.type_of_pay, ta.method, ta.created_at, COALESCE(i.status, ia.status) AS status, ta.reason, ta.paid_date, ta.is_linked
                FROM transactions_archive AS ta
                LEFT JOIN clients AS c ON ta.client_id = c.client_id
                LEFT JOIN clients_archive AS ca ON ta.client_id = ca.client_id
                LEFT JOIN inventory AS i ON ta.item_id = i.item_id
                LEFT JOIN items_archive AS ia ON ta.item_id = ia.item_id
                INNER JOIN branches AS b ON ta.branch_id = b.branch_id
                INNER JOIN users AS u ON ta.created_by = u.username
                WHERE ta.transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $archiver = htmlspecialchars($row['archived_by']);
            $arch_date = htmlspecialchars($row['archived_date']);
            $a_agreement = htmlspecialchars($row['agreement_num']);
            $a_client_name = htmlspecialchars($row['fullname']);
            $a_fetch_item_id = htmlspecialchars($row['item_id']);
            $a_item_name = htmlspecialchars($row['item_name']);
            $a_fetch_br_id = htmlspecialchars($row['branch_id']);
            $a_transac_branch = htmlspecialchars($row['branch_name']);
            $a_transac_amount = htmlspecialchars($row['amount']);
            $a_transac_type = htmlspecialchars($row['type_of_pay']);
            $a_transac_method = htmlspecialchars($row['method']);
            $a_transac_date = htmlspecialchars($row['created_at']);
            $a_status = htmlspecialchars($row['status']);
            $a_transac_reason = htmlspecialchars($row['reason']);
            $a_transac_p_date = htmlspecialchars($row['paid_date']);
            $a_is_linked = htmlspecialchars($row['is_linked']);
        }

        $audit_u_id = $_SESSION['user_id'];
        $audit_action = "Accessed";
        $audit_obj = "Transaction";
        $audit_desc = "Accessed archived transaction for agreement no. $a_agreement";

        $curDate = new DateTime();
        $current = $curDate->format('Y-m-d H:i:s');

        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $a_fetch_br_id, $current);
        $stmt->execute(); 

        $createDate = new DateTime($a_transac_date);
        $a_transac_created = $createDate->format("F j, Y");
        $archDate = new DateTime($arch_date);
        $archived_at = $archDate->format("F j, Y");

        $sql = "SELECT u.fullname AS creator, u.username FROM transactions_archive AS ta
                INNER JOIN users AS u ON ta.created_by = u.username
                WHERE ta.transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $a_creator_name = htmlspecialchars($row['creator']);
        }

        if($archiver != "system")
        {
            $sql = "SELECT u.fullname AS archiver, u.username FROM transactions_archive AS ta
                    INNER JOIN users AS u ON ta.archived_by = u.username
                    WHERE ta.transaction_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $transaction_id);
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

        switch ($a_transac_branch)
        {
            case "Marikina-Pasig":
                $branch_acro = "MP";
                break;
            case "Quezon City":
                $branch_acro = "Q";
                break;
            case "Makati":
                $branch_acro = "M";
                break;
        }
        
        echo "<title>$branch_acro$a_agreement's Details</title>"
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
                        <h1>Transaction Details</h1>
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
                                echo "<h2>$branch_acro$a_agreement's Transaction Information</h2>";
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='archived_transactions.php'"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created by: <?php echo $a_creator_name; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Created at: <?php echo $a_transac_created; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Archived by: <?php echo $arch_name; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Archived at: <?php echo $archived_at; ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Archived reason: <?php echo $a_transac_reason; ?> </span>
                                </div>
                            </div>
                            <hr>
                            <form action="" method="POST">
                                <div class="archive_btn_cont">
                                    <?php
                                        if($is_readonly == 0)
                                        {
                                            echo '<button type="submit" name="submit"><img src="../resources/img/icons/unarchive_w.png" alt="unarchive">Restore</button>';
                                        }
                                    ?>
                                    <?php 
                                        if (isset($_POST['delete']))
                                        {
                                            if($a_is_linked == 0)
                                            {
                                                $sql = "DELETE FROM transactions_archive WHERE transaction_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("i", $transaction_id);
                                                if($stmt->execute())
                                                {
                                                    $audit_u_id = $_SESSION['user_id'];
                                                    $audit_action = "Deleted";
                                                    $audit_obj = "Archived Transaction";
                                                    $audit_desc= "Deleted archived transaction for agreement no. $a_agreement";
                                                    
                                                    $curDate = new DateTime();
                                                    $current = $curDate->format('Y-m-d H:i:s');
                                                    
                                                    $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp) 
                                                            VALUES (?, ?, ?, ?, ?, ?)";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $fetch_b_id, $current);
                                                    if($stmt->execute())
                                                    {
                                                        $_SESSION['renew_success_msg'] = "Archived Transaction has been deleted.";
                                                        
                                                        header("Location: ../archives/archived_transactions.php");
                                                        exit();
                                                    }
                                                }
                                            } 
                                            else if($a_is_linked == 1)
                                            {
                                                $sql = "DELETE FROM transactions_archive WHERE item_id = ? AND archived_date = ? AND is_linked = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("isi", $a_fetch_item_id, $arch_date, $a_is_linked); 
                                                if($stmt->execute())
                                                {
                                                    $audit_u_id = $_SESSION['user_id'];
                                                    $audit_action = "Deleted";
                                                    $audit_obj = "Archived Transaction";
                                                    $audit_desc= "Deleted archived split transaction for agreement no. $a_agreement";
                                                    
                                                    $curDate = new DateTime();
                                                    $current = $curDate->format('Y-m-d H:i:s');
                                                    
                                                    $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp) 
                                                            VALUES (?, ?, ?, ?, ?, ?)";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $a_fetch_br_id, $current);
                                                    if($stmt->execute())
                                                    {
                                                        $_SESSION['renew_success_msg'] = "Archived Split Transaction has been deleted.";
                                                        
                                                        header("Location: ../archives/archived_transactions.php");
                                                        exit();
                                                    }
                                                }
                                            }
                                        }
                                        
                                        if ($role === "admin")
                                        {
                                            echo '<br><hr>
                                                <button type="submit" name="delete" onclick="return confirm(\'This process cannot be undone. Are you sure you want to proceed?\');"><img src="../resources/img/icons/delete_forever_w.png" alt="delete">Delete Permanently</button>
                                                <div class="archive_text">
                                                    <span class="message_warning"><img src="../resources/img/icons/warning.png" alt="warning">Deleting this data cannot be undone.</span>
                                                </div>';
                                        } else {
                                            echo '<button type="submit" name="req_delete"><img src="../resources/img/icons/reminder_blue.png" alt="request">Request Deletion</button>';
                                        }

                                        if(isset($_POST['req_delete']))
                                        {
                                            $tbl_name = "transactions_archive";
                                            $rec_id = $transaction_id;

                                            $req_check_sql = "SELECT * FROM deletion_request WHERE table_name = ? AND record_id = ?";
                                            $req_c_stmt = $conn->prepare($req_check_sql);
                                            $req_c_stmt->bind_param("si", $tbl_name, $rec_id);
                                            $req_c_stmt->execute();
                                            $req_c_res = $req_c_stmt->get_result();

                                            if($req_c_res->num_rows == 0)
                                            {
                                                $requester = $_SESSION['username'];
                                                $req_b_id = $_SESSION['branch_id'];
                                                $req_reason = $a_transac_reason;
                                                $req_status = "Pending";

                                                $reqDate = new DateTime();
                                                $req_date = $reqDate->format('Y-m-d H:i:s');

                                                $req_sql = "INSERT INTO deletion_request (table_name, record_id, requested_by, branch_id, reason, status, requested_at)
                                                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                                                $req_stmt = $conn->prepare($req_sql);
                                                $req_stmt->bind_param("sisisss", $tbl_name, $rec_id, $requester, $req_b_id, $req_reason, $req_status, $req_date);
                                                if($req_stmt->execute())
                                                {
                                                    $_SESSION['req_success_msg'] = "Deletion request sent!";

                                                    header("Location: ../archives/archived_transactions.php");
                                                    exit(); 
                                                }
                                            }
                                            else 
                                            {
                                                $_SESSION['req_error_msg'] = "There is already a pending deletion request for this record.";

                                                header("Location: ../archives/archived_transactions.php");
                                                exit(); 
                                            }
                                        }
                                    ?>
                                </div>
                            </form>
                        </div>
                        <div class="details_editable">
                            <form action="" method="POST" class="editable_item_section">
                                <div class="item_info_detail_row">
                                    <label for="agreement_num">Agreement Number</label>
                                    <input type="text" name="agreement_num" id="agreement_num" class="item_tags_disabled" value="<?php echo $a_agreement; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" name="item_name" id="item_name" class="item_tags" value="<?php echo $a_item_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" class="item_tags" value="<?php echo $a_client_name; ?>" disabled>
                                </div>
                                <?php
                                    if ($role === "admin") 
                                    {
                                        echo   '<div class="item_info_detail_row">
                                                    <label for="branch_name">Branch</label>
                                                    <input type="text" name="branch_name" id="branch_name" class="item_tags" value="'; echo $a_transac_branch; echo '" disabled>
                                                </div>';
                                    }
                                
                                    if($a_is_linked == 0)
                                    {
                                        echo '<div class="item_info_detail_row">
                                                <label for="method">Method of Payment</label>
                                                <input type="text" name="method" id="method" class="item_tags" value="'; echo $a_transac_method; echo '" disabled>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="amount">Amount</label>
                                                  <input type="text" name="amount" id="amount" class="item_tags" value="'; echo $a_transac_amount; echo '" disabled>
                                              </div>';
                                    }
                                    else if($a_is_linked == 1)
                                    {
                                        $sql = "SELECT transaction_id, amount, method FROM transactions_archive WHERE item_id = ? AND branch_id = ? AND paid_date = ? AND transaction_id != ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("iisi", $a_fetch_item_id, $a_fetch_br_id, $a_transac_p_date, $transaction_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $linked = $result->fetch_assoc();

                                        $a_transac_id2 = htmlspecialchars($linked['transaction_id']);
                                        $a_transac_amount2 = htmlspecialchars($linked['amount']);
                                        $a_transac_method2 = htmlspecialchars($linked['method']);

                                        echo '<div class="item_info_detail_row">
                                                <label for="method">Method of Payment #1</label>
                                                <input type="text" name="method" id="method" class="item_tags" value="'; echo $a_transac_method; echo '" disabled>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="amount">Amount Paid (First Payment)</label>
                                                  <input type="text" name="amount" id="amount" class="item_tags" value="'; echo $a_transac_amount; echo '" disabled>
                                              </div>
                                              <div class="item_info_detail_row">
                                                <label for="method2">Method of Payment #2</label>
                                                <input type="text" name="method2" id="method2" class="item_tags" value="'; echo $a_transac_method2; echo '" disabled>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="amount2">Amount Paid (First Payment)</label>
                                                  <input type="text" name="amount2" id="amount2" class="item_tags" value="'; echo $a_transac_amount2; echo '" disabled>
                                              </div>';
                                    }
                                ?>
                                <div class="item_info_detail_row">
                                    <label for="type">Type of Payment</label>
                                    <input type="text" name="type" id="type" class="item_tags" value="<?php echo $a_transac_type; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="type">Date moved by transaction</label>
                                    <input type="text" name="type" id="type" class="item_tags" value="<?php echo $a_transac_p_date; ?>" disabled>
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
                                if(isset($_POST['submit']))
                                {
                                    // Check muna si items_archive
                                    $sql = "SELECT * FROM items_archive WHERE item_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $a_fetch_item_id);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if($result->num_rows == 0)
                                    {
                                        $sql = "SELECT due_date FROM inventory WHERE item_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $a_fetch_item_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        
                                        if($result->num_rows > 0) 
                                        {
                                            $row = $result->fetch_assoc();
                                            $current_due = htmlspecialchars($row['due_date']);
                                        }

                                        // Removing from archive
                                        $sql = "SELECT transaction_id, agreement_num, item_id, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                                                FROM transactions_archive
                                                WHERE transaction_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $transaction_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if($result->num_rows > 0) 
                                        {
                                            $row = $result->fetch_assoc();
                                            $transArch_id = htmlspecialchars($row['transaction_id']);
                                            $transArch_agreement = htmlspecialchars($row['agreement_num']);
                                            $transArch_i_id = htmlspecialchars($row['item_id']);
                                            $transArch_c_id = htmlspecialchars($row['client_id']);
                                            $transArch_b_id = htmlspecialchars($row['branch_id']);
                                            $transArch_amt = htmlspecialchars($row['amount']);
                                            $transArch_type = htmlspecialchars($row['type_of_pay']);
                                            $transArch_creator = htmlspecialchars($row['created_by']);
                                            $transArch_c_at = htmlspecialchars($row['created_at']);
                                            $transArch_e_at = htmlspecialchars($row['edited_at']);
                                            $transArch_method = htmlspecialchars($row['method']);
                                            $transArch_p_date = htmlspecialchars($row['paid_date']);
                                            $transArch_is_linked = htmlspecialchars($row['is_linked']);

                                            $fetch_sql = "SELECT MIN(paid_date) AS prev FROM transactions_archive WHERE item_id = ?";
                                            $stmt = $conn->prepare($fetch_sql);
                                            $stmt->bind_param("i", $transArch_i_id);
                                            $stmt->execute();
                                            $fetch_result = $stmt->get_result();

                                            if($fetch_result->num_rows > 0) 
                                            {
                                                $fetch_row = $fetch_result->fetch_assoc();
                                                $first_p_date = htmlspecialchars($fetch_row['prev']);
                                            }

                                            //tignan muna if existing na ung transaction
                                            $check_sql = "SELECT * FROM transactions WHERE item_id = ? AND paid_date = ?";
                                            $stmt = $conn->prepare($check_sql);
                                            $stmt->bind_param("is", $transArch_i_id, $transArch_p_date);
                                            $stmt->execute();
                                            $check_result = $stmt->get_result();
                                            
                                            if($check_result->num_rows == 0) // meaning wlang dupe
                                            {
                                                $paid_date = new DateTime($transArch_p_date);
                                                $cur_due = new DateTime($current_due);

                                                if($cur_due == $paid_date) //check if pareho parin ung paid_date dun sa current na due date nung item
                                                {
                                                    if($transArch_p_date == $first_p_date) //check if ung pinaka-unang paid date ung nakaselect
                                                    {
                                                        $unarchiver = $_SESSION['username'];
                                                        if($transArch_type == "Interest")
                                                        {
                                                            if($a_status != "Redeemed")
                                                            {
                                                                //kukunin nya ung creation date ska paid date
                                                                $create_date = new DateTime($transArch_c_at);
                                                                $paid = new DateTime($transArch_p_date);

                                                                //If mas malaki ung create kesa sa paid (meaning late na nabayaran ung item)
                                                                if($create_date > $paid)
                                                                {
                                                                    $create_date->modify('+30days');
                                                                    $set = $create_date;
                                                                    $check_again = true;
                                                                    $set_date = $create_date->format('Y-m-d H:i:s');
                                                                }
                                                                else // (meaning inde pa overdue nung mabayaran)
                                                                {
                                                                    $paid->modify('+30days');
                                                                    $set = $paid;
                                                                    $check_again = false;
                                                                    $set_date = $paid->format('Y-m-d H:i:s');
                                                                }

                                                                $curDate = new DateTime();
                                                                $current = $curDate->format('Y-m-d H:i:s');
                                                                if($a_status != "Redeemed")
                                                                {
                                                                    if($set < $curDate)
                                                                    {
                                                                        $item_status = "Overdue";
                                                                    }
                                                                    else
                                                                    {
                                                                        $item_status = "Active";
                                                                    }
                                                                }

                                                                // check if ung created_at nung transaction is less sa current date (after nung +30days)
                                                                if($check_again === true && $set < $curDate)
                                                                {
                                                                    $set_date = $current;
                                                                }

                                                                //Update si Inventory
                                                                $sql = "UPDATE inventory
                                                                        SET due_date = ?, status = ?, updated_at = ?, updated_by = ?
                                                                        WHERE item_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("ssssi", $set_date, $item_status, $current, $unarchiver, $transArch_i_id);
                                                                $stmt->execute();
                                                            }
                                                            else 
                                                            {
                                                                $_SESSION['error_msg'] = "The item has already been redeemed.";

                                                                header("Location: " . $_SERVER['REQUEST_URI']);
                                                                exit();
                                                            }
                                                        }
                                                        else if($transArch_type == "Principal")
                                                        {
                                                            $item_status = "Redeemed";
                                                            
                                                            $sql = "UPDATE inventory
                                                                    SET status = ?, updated_at = ?, updated_by = ?
                                                                    WHERE item_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("sssi", $item_status, $current, $unarchiver, $transArch_i_id);
                                                            $stmt->execute();
                                                        }

                                                        if($transArch_is_linked == 0)
                                                        {
                                                            $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("i", $transArch_b_id);
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            $row = $result->fetch_assoc();

                                                            $fetch_eb = (float)htmlspecialchars($row['end_balance']);
                                                            $upd_success = false;

                                                            $sql = "UPDATE branches
                                                                    SET end_balance = ?
                                                                    WHERE branch_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            if(isset($fetch_eb))
                                                            {
                                                                if($transArch_method == "Cash")
                                                                {
                                                                    $new_eb_val = $fetch_eb + (float)$transArch_amt; 
                                                                    $stmt->bind_param("di", $new_eb_val, $transArch_b_id);
                                                                    if($stmt->execute())
                                                                    {
                                                                        $upd_success = true;
                                                                    }
                                                                }
                                                                else 
                                                                {
                                                                    $new_eb_val = ($fetch_eb + (float)$transArch_amt) - (float)$transArch_amt; //no change (cancel out)
                                                                    $stmt->bind_param("di", $new_eb_val, $transArch_b_id);
                                                                    if($stmt->execute())
                                                                    {
                                                                        $upd_success = true;
                                                                    }
                                                                }
                                                            }
                                                            else 
                                                            {
                                                                $upd_success = true;
                                                            }

                                                            if($upd_success)
                                                            {
                                                                //insert ule sa transactions
                                                                $sql = "INSERT INTO transactions (transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked)
                                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("iiiiidssssssi", $transArch_id, $transArch_agreement, $transArch_c_id, $transArch_b_id, $transArch_i_id, $transArch_amt, $transArch_type, $transArch_creator, $transArch_c_at, $transArch_e_at, $transArch_method, $transArch_p_date, $transArch_is_linked);
                                                                if($stmt->execute())
                                                                {
                                                                    //Delete sa transac
                                                                    $sql = "DELETE FROM transactions_archive WHERE transaction_id = ?";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bind_param("i", $transaction_id);
                                                                    $stmt->execute();

                                                                    $audit_u_id = $_SESSION['user_id'];
                                                                    $audit_action = "Archive";
                                                                    $audit_obj = "Transaction";
                                                                    $audit_desc = "Restored $transArch_type transaction for agreement no. $transArch_agreement";

                                                                    $curDate = new DateTime();
                                                                    $current = $curDate->format('Y-m-d H:i:s');

                                                                    $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                            VALUES (?, ?, ?, ?, ?, ?)";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $transArch_b_id, $current);
                                                                    $stmt->execute();
                                                                }
                                                            }
                                                        }
                                                        else if($transArch_is_linked == 1)
                                                        {
                                                            $transArch_pay_parts = 
                                                            [
                                                                [
                                                                    'transac_id' => $transaction_id,
                                                                    'amount' => $a_transac_amount,
                                                                    'method' => $a_transac_method
                                                                ],
                                                                [
                                                                    'transac_id' => $a_transac_id2,
                                                                    'amount' => $a_transac_amount2,
                                                                    'method' => $a_transac_method2
                                                                ]
                                                            ];

                                                            $count = 1;
                                                            foreach ($transArch_pay_parts as $transArch_part) 
                                                            {
                                                                $transArch_part_id = $transArch_part['transac_id'];
                                                                $transArch_part_amount = $transArch_part['amount'];
                                                                $transArch_part_method = $transArch_part['method'];

                                                                $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("i", $transArch_b_id);
                                                                $stmt->execute();
                                                                $result = $stmt->get_result();
                                                                $row = $result->fetch_assoc();

                                                                $fetch_eb = (float)htmlspecialchars($row['end_balance']);
                                                                $upd_success = false;

                                                                $sql = "UPDATE branches
                                                                        SET end_balance = ?
                                                                        WHERE branch_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                if(isset($fetch_eb))
                                                                {
                                                                    if($transArch_part_method == "Cash")
                                                                    {
                                                                        $new_eb_val = $fetch_eb + (float)$transArch_part_amount; 
                                                                        $stmt->bind_param("di", $new_eb_val, $transArch_b_id);
                                                                        if($stmt->execute())
                                                                        {
                                                                            $upd_success = true;
                                                                        }
                                                                    }
                                                                    else 
                                                                    {
                                                                        $new_eb_val = ($fetch_eb + (float)$transArch_part_amount) - (float)$transArch_part_amount; //no change (cancel out)
                                                                        $stmt->bind_param("di", $new_eb_val, $transArch_b_id);
                                                                        if($stmt->execute())
                                                                        {
                                                                            $upd_success = true;
                                                                        }
                                                                    }
                                                                }
                                                                else 
                                                                {
                                                                    $upd_success = true;
                                                                }

                                                                if($upd_success)
                                                                {
                                                                    $sql = "INSERT INTO transactions (transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked)
                                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bind_param("iiiiidssssssi", $transArch_part_id, $transArch_agreement, $transArch_c_id, $transArch_b_id, $transArch_i_id, $transArch_part_amount, $transArch_type, $transArch_creator, $transArch_c_at, $transArch_e_at, $transArch_part_method, $transArch_p_date, $transArch_is_linked);
                                                                    if($stmt->execute())
                                                                    {
                                                                        //Delete sa transac
                                                                        $sql = "DELETE FROM transactions_archive WHERE transaction_id = ?";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->bind_param("i", $transArch_part_id);
                                                                        $stmt->execute();

                                                                        if($count == 1)
                                                                        {
                                                                            $audit_u_id = $_SESSION['user_id'];
                                                                            $audit_action = "Archive";
                                                                            $audit_obj = "Transaction";
                                                                            $audit_desc = "Restored split $transArch_type transaction for agreement no. $transArch_agreement";

                                                                            $curDate = new DateTime();
                                                                            $current = $curDate->format('Y-m-d H:i:s');

                                                                            $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $transArch_b_id, $current);
                                                                            $stmt->execute();
                                                                        }
                                                                    }
                                                                }
                                                                $count++;
                                                            }
                                                        }
                                                    }
                                                    else 
                                                    {
                                                        $_SESSION['error_msg'] = "Please unarchive the previous transaction for this agreement number first.";

                                                        header("Location: " . $_SERVER['REQUEST_URI']);
                                                        exit();
                                                    }
                                                }
                                                else
                                                {
                                                    $_SESSION['error_msg'] = "Current due date doesn't match with the date of payment. Check for previous transactions (If there are none, please delete this one)";

                                                    header("Location: " . $_SERVER['REQUEST_URI']);
                                                    exit();
                                                }
                                            }
                                            else 
                                            {
                                                $_SESSION['error_msg'] = "There is already an existing transaction (Please delete this one).";

                                                header("Location: " . $_SERVER['REQUEST_URI']);
                                                exit();
                                            }
                                        }
                                        
                                        $_SESSION['renew_success_msg'] = "Transaction successfully restored.";

                                        header("Location: ../archives/archived_transactions.php");
                                        exit(); 
                                    }
                                    else 
                                    {
                                        $_SESSION['error_msg'] = "This item has already been redeemed or liquidated.";

                                        header("Location: " . $_SERVER['REQUEST_URI']);
                                        exit(); 
                                    }
                                }
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
    <div id="overlay" class="overlay_hidden"></div>
    <div id="popup" class="popup_hidden">
        <div class="popup_content">
            <div class="popup_top">
                <h2>Reason for archiving</h2>
                <button id="closePopup"><img src="../resources/img/icons/close.png" alt="close"></button>
            </div>
            <hr>
            <div class="popup_main">
                <div class="card_form">
                    <p>Please select a reason for archiving this item.</p>
                    <form action="" method="">
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
                                <td colspan="2"><textarea style="resize: none; font-size: 15px;" rows="4" cols="50" placeholder="If cases above don't apply, type reason here."></textarea></td>
                            </tr>
                        </table>
                        <div class="modal-actions">
                            <button type="submit" class="btn-proceed"><img src="../resources/img/icons/arrow_circle_right.png" alt="proceed">Proceed to Archive</button>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
    <script src="../resources/js/fetch_agreement.js"></script>
    <script src="../resources/js/event_buttonEdit.js"></script>
    <!-- <script>
        /*dat iload muna lahat ng page ung needed elements bago maclick eto*/
        document.addEventListener("DOMContentLoaded", function() {
            /*hanapin nya muna si button sa .archive_btn_cont*/
            /*hanapin din si overlay tas popup then yung close button*/
            const openBtn = document.querySelector(".details_info .archive_btn_cont button");
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
    </script> -->
</body>
</html>