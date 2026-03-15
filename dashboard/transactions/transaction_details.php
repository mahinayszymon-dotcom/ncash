<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
include("../../db/branch_fetch.php");
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

        $sql = "SELECT t.agreement_num, c.fullname, t.item_id, i.item_name, i.principal, t.branch_id, b.branch_name, t.amount, t.type_of_pay, t.method, t.created_at, u.fullname AS creator, i.status, t.paid_date, t.is_linked
                FROM transactions AS t
                INNER JOIN clients AS c ON t.client_id = c.client_id
                INNER JOIN inventory AS i ON t.item_id = i.item_id
                INNER JOIN branches AS b ON t.branch_id = b.branch_id
                INNER JOIN users AS u ON t.created_by = u.username
                WHERE t.transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $agreement_num = htmlspecialchars($row['agreement_num']);
            $client_name = htmlspecialchars($row['fullname']);
            $item_id = htmlspecialchars($row['item_id']);
            $item_name = htmlspecialchars($row['item_name']);
            $item_principal = htmlspecialchars($row['principal']);
            $transac_br_id = htmlspecialchars($row['branch_id']);
            $transac_branch = htmlspecialchars($row['branch_name']);
            $transac_amount = htmlspecialchars($row['amount']);
            $transac_type = htmlspecialchars($row['type_of_pay']);
            $transac_method = htmlspecialchars($row['method']);
            $transac_date = htmlspecialchars($row['created_at']);
            $transac_creator = htmlspecialchars($row['creator']);
            $status = htmlspecialchars($row['status']);
            $transac_p_date = htmlspecialchars($row['paid_date']);
            $is_linked = htmlspecialchars($row['is_linked']);
        }

        $audit_u_id = $_SESSION['user_id'];
        $audit_action = "Accessed";
        $audit_obj = "Transaction";
        $audit_desc = "Accessed transaction for agreement no. $agreement_num";

        $curDate = new DateTime();
        $current = $curDate->format('Y-m-d H:i:s');

        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $transac_br_id, $current);
        $stmt->execute(); 

        $createDate = new DateTime($transac_date);
        $transac_created = $createDate->format("F j, Y");
        $principal_decimal = number_format($item_principal, 2);

        switch ($transac_branch)
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
        
        echo "<title>$branch_acro$agreement_num's Transaction Details</title>"
    ?>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/transactions.css">
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
                        <h1>Transaction Details</h1>
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
            <div class="central_panelC">
                <div class="data_controlsB">
                    <div class="data_controls_header">
                        <div class="data_controls_header_text">
                            <div class="icon_normal">
                                <img src="../../resources/img/icons/description_w.png" alt="description">
                            </div>
                            <?php 
                                echo "<h2>$branch_acro$agreement_num's Transaction Information</h2>";
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../transactions.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created by: <?php echo $transac_creator ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Created at: <?php echo $transac_created ?> </span>
                                </div>
                            </div>
                            <hr>
                            <div class="archive_btn_cont">
                                <button type="submit" name="submit"><img src="../../resources/img/icons/archive_w.png" alt="archive">Archive this transaction</button>
                                <div class="archive_text">
                                    <span class="message_info"><img src="../../resources/img/icons/info.png" alt="info">Archiving this transaction will move it to a separate list and hide it from active view in this module.</span>
                                </div>
                            </div>
                        </div>
                        <div class="details_editable">
                            <form action="" method="POST" class="editable_item_section">
                                <div class="item_info_detail_row">
                                    <label for="agreement_num">Agreement Number</label>
                                    <input type="text" name="agreement_num" id="agreement_num" class="item_tags" value="<?php echo $agreement_num; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" name="item_name" id="item_name" class="item_tags" value="<?php echo $item_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" class="item_tags" value="<?php echo $client_name; ?>" disabled>
                                </div>
                                <?php
                                    if ($role === "admin") {
                                        echo    '<div class="item_info_detail_row">
                                                    <label for="branch_select">Branch</label>
                                                    <select name="branch_select" id="branch_select" class="item_tags" required onchange="updateAgreement()" disabled>
                                                        <option value="" disabled>--Select Branch--</option>
                                                        <option value="1100"'; echo ($transac_branch == "Marikina-Pasig") ? "selected" : ""; echo '>Marikina-Pasig Branch</option>
                                                        <option value="1101"'; echo ($transac_branch == "Quezon City") ? "selected" : ""; echo '>Quezon City Branch</option>
                                                        <option value="1102"'; echo ($transac_branch == "Makati") ? "selected" : ""; echo '>Makati Branch</option>
                                                    </select>
                                                </div>';
                                    }
                                
                                    if($is_linked == 0)
                                    {
                                        echo '<div class="item_info_detail_row">
                                                  <label for="mode_of_payment">Method of Payment</label>
                                                  <select name="mode_of_payment" id="mode_of_payment" class="item_tags" required disabled>
                                                      <option value="" disabled>--Select Method--</option>
                                                      <option value="Cash"'; echo ($transac_method == "Cash") ? "selected" : ""; echo '>Cash</option>
                                                      <option value="Online"'; echo ($transac_method == "Online") ? "selected" : ""; echo '>Online</option>
                                                      <option value="Bank"'; echo ($transac_method == "Bank") ? "selected" : ""; echo '>Bank</option>
                                                  </select>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="principal">Amount Paid</label>
                                                  <input type="text" name="amount" id="amount" class="item_tags" value="'; echo $transac_amount; echo '" disabled>
                                              </div>';
                                    }
                                    else if($is_linked == 1)
                                    {
                                        $sql = "SELECT transaction_id, amount, method FROM transactions WHERE item_id = ? AND branch_id = ? AND paid_date = ? AND transaction_id != ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("iisi", $item_id, $transac_br_id, $transac_p_date, $transaction_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        $linked = $result->fetch_assoc();

                                        $transac_id2 = htmlspecialchars($linked['transaction_id']);
                                        $transac_amount2 = htmlspecialchars($linked['amount']);
                                        $transac_method2 = htmlspecialchars($linked['method']);

                                        echo '<div class="item_info_detail_row">
                                                  <label for="mode_of_payment">Method of Payment #1</label>
                                                  <select name="mode_of_payment" id="mode_of_payment" class="item_tags" required disabled>
                                                      <option value="" disabled>--Select Method--</option>
                                                      <option value="Cash"'; echo ($transac_method == "Cash") ? "selected" : ""; echo '>Cash</option>
                                                      <option value="Online"'; echo ($transac_method == "Online") ? "selected" : ""; echo '>Online</option>
                                                      <option value="Bank"'; echo ($transac_method == "Bank") ? "selected" : ""; echo '>Bank</option>
                                                  </select>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="principal">Amount Paid (First Payment)</label>
                                                  <input type="text" name="amount" id="amount" class="item_tags" value="'; echo $transac_amount; echo '" disabled>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="mode_of_payment2">Method of Payment #2</label>
                                                  <select name="mode_of_payment2" id="mode_of_payment2" class="item_tags" required disabled>
                                                      <option value="" disabled>--Select Method--</option>
                                                      <option value="Cash"'; echo ($transac_method2 == "Cash") ? "selected" : ""; echo '>Cash</option>
                                                      <option value="Online"'; echo ($transac_method2 == "Online") ? "selected" : ""; echo '>Online</option>
                                                      <option value="Bank"'; echo ($transac_method2 == "Bank") ? "selected" : ""; echo '>Bank</option>
                                                  </select>
                                              </div>
                                              <div class="item_info_detail_row">
                                                  <label for="amount2">Amount Paid (Second Payment)</label>
                                                  <input type="text" name="amount2" id="amount2" class="item_tags" value="'; echo $transac_amount2; echo '" disabled>
                                              </div>';
                                    }
                                ?>
                                <div class="item_info_detail_row">
                                    <label for="type_of_payment">Type of Payment</label>
                                    <select name="type_of_payment" id="type_of_payment" required disabled>
                                        <option value="" disabled>--Select Type--</option>
                                        <option value="Principal" <?php echo ($transac_type == "Principal") ? "selected" : ""; ?>>For Redemption (Principal)</option>
                                        <option value="Interest" <?php echo ($transac_type == "Interest") ? "selected" : ""; ?>>For Renewal (Interest)</option>
                                    </select>
                                </div>  
                                <div class="item_info_detail_row">
                                    <label for="principal"> Item Principal</label>
                                    <input type="text" name="principal" id="principal" class="item_tags" value="<?php echo $principal_decimal; ?>" disabled>
                                </div>
                            </form>
                            <div class="result_cont">
                                <?php
                                    // $_SESSION['change_success_msg'] = '';

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
                        </div>
                        <?php
                            try
                            {
                                // logic mo
                                
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
                <button id="closePopup"><img src="../../resources/img/icons/close.png" alt="close"></button>
            </div>
            <hr>
            <div class="popup_main">
                <div class="card_form">
                    <p>Please select a reason for archiving this transaction.</p>
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
                <?php 
                    if (isset($_POST['proceed'])) {
                        if (isset($_POST['reason']) && (isset($_POST['custom_reason']) && !empty(trim($_POST['custom_reason']))))
                        {
                            $_SESSION['error_msg'] = "Please state the reason either by choosing or entering the reason, not both";

                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit(); 
                        }
                        else if (isset($_POST['reason']) || (isset($_POST['custom_reason']) && !empty(trim($_POST['custom_reason'])))) 
                        {
                            $transac_archiver = $_SESSION['username'];
                            $success_str = "Transaction successfully archived!";

                            // Transaction archiving
                            $sql = "SELECT transaction_id, agreement_num, item_id, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                                    FROM transactions
                                    WHERE transaction_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $transaction_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            if($result->num_rows > 0) 
                            {
                                $row = $result->fetch_assoc();
                                $transac_id = htmlspecialchars($row['transaction_id']);
                                $transac_agreement = htmlspecialchars($row['agreement_num']);
                                $transac_i_id = htmlspecialchars($row['item_id']);
                                $transac_c_id = htmlspecialchars($row['client_id']);
                                $transac_b_id = htmlspecialchars($row['branch_id']);
                                $transac_amt = htmlspecialchars($row['amount']);
                                $transac_type = htmlspecialchars($row['type_of_pay']);
                                $transac_creator = htmlspecialchars($row['created_by']);
                                $transac_c_at = htmlspecialchars($row['created_at']);
                                $transac_e_at = htmlspecialchars($row['edited_at']);
                                $transac_method = htmlspecialchars($row['method']);
                                $transac_p_date = htmlspecialchars($row['paid_date']);
                                $transac_is_linked = htmlspecialchars($row['is_linked']);

                                $fetch_sql = "SELECT MAX(paid_date) AS latest from transactions WHERE item_id = ?";
                                $stmt = $conn->prepare($fetch_sql);
                                $stmt->bind_param("i", $transac_i_id);
                                $stmt->execute();
                                $fetch_result = $stmt->get_result();

                                if($fetch_result->num_rows > 0)
                                {
                                    $fetch_row = $fetch_result->fetch_assoc();
                                    $latest = htmlspecialchars($fetch_row['latest']);
                                }

                                if($transac_p_date == $latest)
                                {
                                    if($transac_type == "Interest")
                                    {
                                        $prevDate = new DateTime($transac_p_date);
                                        $curDate = new DateTime();
                                        if($status != "Redeemed")
                                        {
                                            if($prevDate < $curDate)
                                            {
                                                $item_status = "Overdue";
                                            }
                                            else
                                            {
                                                $item_status = "Active";
                                            }
                                        }

                                        $current = $curDate->format('Y-m-d H:i:s');

                                        //Update si Inventory
                                        $sql = "UPDATE inventory
                                                SET due_date = ?, status = ?, updated_at = ?, updated_by = ?
                                                WHERE item_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("ssssi", $transac_p_date, $item_status, $current, $transac_archiver, $transac_i_id);
                                        $stmt->execute();
                                    }
                                    else if($transac_type == "Principal")
                                    {
                                        $item_status = "Active";
                                        
                                        $sql = "UPDATE inventory
                                                SET due_date = ?, status = ?, updated_at = ?, updated_by = ?
                                                WHERE item_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("ssssi", $transac_p_date, $item_status, $current, $transac_archiver, $transac_i_id);
                                        $stmt->execute();
                                    }

                                    //insert si transac sa archive
                                    $transac_archiver = $_SESSION['username'];
                                    if(isset($_POST['custom_reason']) && !empty(trim($_POST['custom_reason']))) //For the reason in the archive
                                    {
                                        $transac_reason = htmlspecialchars($_POST['custom_reason']);
                                    }
                                    else if(isset($_POST['reason']))
                                    {
                                        $transac_reason = htmlspecialchars($_POST['reason']);
                                    }

                                    if($transac_is_linked == 0)
                                    {
                                        $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $transac_b_id);
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
                                            if($transac_method == "Cash")
                                            {
                                                $new_eb_val = $fetch_eb - (float)$transac_amt; 
                                                $stmt->bind_param("di", $new_eb_val, $transac_b_id);
                                                if($stmt->execute())
                                                {
                                                    $upd_success = true;
                                                }
                                            }
                                            else 
                                            {
                                                $new_eb_val = ($fetch_eb + (float)$transac_amt) - (float)$transac_amt; //no change (cancel out)
                                                $stmt->bind_param("di", $new_eb_val, $transac_b_id);
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
                                            $sql = "INSERT INTO transactions_archive (archived_by, archived_date, transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked, reason)
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("ssiiiiidssssssis", $transac_archiver, $current, $transac_id, $transac_agreement, $transac_c_id, $transac_b_id, $transac_i_id, $transac_amt, $transac_type, $transac_creator, $transac_c_at, $transac_e_at, $transac_method, $transac_p_date, $transac_is_linked, $transac_reason);
                                            if($stmt->execute())
                                            {
                                                //Delete sa transac
                                                $sql = "DELETE FROM transactions WHERE transaction_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("i", $transac_id);
                                                $stmt->execute();

                                                $audit_u_id = $_SESSION['user_id'];
                                                $audit_action = "Archive";
                                                $audit_obj = "Transaction";
                                                $audit_desc = "Archived $transac_type transaction for agreement no. $transac_agreement";

                                                $curDate = new DateTime();
                                                $current = $curDate->format('Y-m-d H:i:s');

                                                $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                        VALUES (?, ?, ?, ?, ?, ?)";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $transac_b_id, $current);
                                                $stmt->execute();
                                            }
                                        }
                                    }
                                    else if($transac_is_linked == 1)
                                    {
                                        $transac_pay_parts = 
                                        [
                                            [
                                                'transac_id' => $transaction_id,
                                                'amount' => $transac_amount,
                                                'method' => $transac_method
                                            ],
                                            [
                                                'transac_id' => $transac_id2,
                                                'amount' => $transac_amount2,
                                                'method' => $transac_method2
                                            ]
                                        ];

                                        $count = 1;
                                        foreach ($transac_pay_parts as $transac_part) 
                                        {
                                            $transac_part_id = $transac_part['transac_id'];
                                            $transac_part_amount = $transac_part['amount'];
                                            $transac_part_method = $transac_part['method'];

                                            $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $transac_b_id);
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
                                                if($transac_part_method == "Cash")
                                                {
                                                    $new_eb_val = $fetch_eb - (float)$transac_part_amount; 
                                                    $stmt->bind_param("di", $new_eb_val, $transac_b_id);
                                                    if($stmt->execute())
                                                    {
                                                        $upd_success = true;
                                                    }
                                                }
                                                else 
                                                {
                                                    $new_eb_val = ($fetch_eb + (float)$transac_part_amount) - (float)$transac_part_amount; //no change (cancel out)
                                                    $stmt->bind_param("di", $new_eb_val, $transac_b_id);
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
                                                $sql = "INSERT INTO transactions_archive (archived_by, archived_date, transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked, reason)
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("ssiiiiidssssssis", $transac_archiver, $current, $transac_part_id, $transac_agreement, $transac_c_id, $transac_b_id, $transac_i_id, $transac_part_amount, $transac_type, $transac_creator, $transac_c_at, $transac_e_at, $transac_part_method, $transac_p_date, $transac_is_linked, $transac_reason);
                                                if($stmt->execute())
                                                {
                                                    //Delete sa transac
                                                    $sql = "DELETE FROM transactions WHERE transaction_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $transac_part_id);
                                                    $stmt->execute();

                                                    if($count == 1)
                                                    {
                                                        $audit_u_id = $_SESSION['user_id'];
                                                        $audit_action = "Archive";
                                                        $audit_obj = "Transaction";
                                                        $audit_desc = "Archived split $transac_type transaction for agreement no. $transac_agreement";

                                                        $curDate = new DateTime();
                                                        $current = $curDate->format('Y-m-d H:i:s');

                                                        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                VALUES (?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $transac_b_id, $current);
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
                                    $_SESSION['error_msg'] = "Please archive the latest transaction for this agreement number first.";

                                    header("Location: " . $_SERVER['REQUEST_URI']);
                                    exit();
                                }
                            }
                            
                            $_SESSION['transac_success_msg'] = "$success_str";

                            header("Location: ../../dashboard/transactions.php");
                            exit(); 
                        } 
                        else 
                        {
                            $_SESSION['error_msg'] = "Please choose or enter the reason for archiving the transaction.";

                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit(); 
                        }
                    }
                ?>
            </div>        
        </div>
    </div>
    <script src="../../resources/js/fetch_agreement.js"></script>
    <script>
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
    </script>
    <!-- <script src="../../resources/js/event_buttonEdit.js"></script> -->
</body>
</html>