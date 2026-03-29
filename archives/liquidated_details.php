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
            $item_id = htmlspecialchars($_GET['id']);
        }
        else 
        {
            header("Location: ../auth/denied.php");
            exit();
        }

        $sql = "SELECT il.liquidated_at, il.agreement_num, COALESCE(c.client_id, ca.client_id) AS client_id, COALESCE(c.fullname, ca.fullname) AS fullname, COALESCE(c.contact, ca.contact) AS contact, COALESCE(c.email, ca.email) AS email, COALESCE(c.address, ca.address) AS address, COALESCE(NULLIF(c.created_at, '0000-00-00 00:00:00'), NULLIF(ca.created_at, '0000-00-00 00:00:00')) AS client_date, 
                       il.item_name, il.principal, b.branch_id, b.branch_name, il.category, il.interest, il.status, il.due_date, il.remarks, il.created_at, il.updated_at
                FROM items_liquidated AS il
                LEFT JOIN clients AS c ON il.client_id = c.client_id
                LEFT JOIN clients_archive AS ca ON il.client_id = ca.client_id
                INNER JOIN branches AS b ON il.branch_id = b.branch_id
                WHERE il.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $lq_date = htmlspecialchars($row['liquidated_at']);
            $lq_agreement = htmlspecialchars($row['agreement_num']);
            $lq_fetch_c_id = htmlspecialchars($row['client_id']);
            $lq_client_name = htmlspecialchars($row['fullname']);
            $lq_contact = htmlspecialchars($row['contact']);
            $lq_email = htmlspecialchars($row['email']);
            $lq_client_addr = htmlspecialchars($row['address']);
            $lq_item_name = htmlspecialchars($row['item_name']);
            $lq_c_create_at = htmlspecialchars($row['client_date']);
            $lq_principal = htmlspecialchars($row['principal']);
            $lq_fetch_b_id = htmlspecialchars($row['branch_id']);
            $lq_item_branch = htmlspecialchars($row['branch_name']);
            $lq_category = htmlspecialchars($row['category']);
            $lq_interest = htmlspecialchars($row['interest']);
            $lq_status = htmlspecialchars($row['status']);
            $lq_due_date = htmlspecialchars($row['due_date']);
            $lq_remarks = htmlspecialchars($row['remarks']);
            $lq_item_created = htmlspecialchars($row['created_at']);
            $lq_item_updated = htmlspecialchars($row['updated_at']);
        }

        $createDate = new DateTime($lq_item_created);
        $lq_created_at = $createDate->format("F j, Y");
        $liquidDate = new DateTime($lq_date);
        $liquidated_at = $liquidDate->format("F j, Y");

        $sql = "SELECT u.fullname AS creator, u.username FROM items_liquidated AS il
                INNER JOIN users AS u ON il.created_by = u.username
                WHERE il.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $lq_creator_name = htmlspecialchars($row['creator']);
            $lq_creator_uname = htmlspecialchars($row['username']);
        }

        $sql = "SELECT u.username AS editor FROM items_liquidated AS il
                INNER JOIN users AS u ON il.updated_by = u.username
                WHERE il.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $lq_editor_uname = htmlspecialchars($row['editor']);
        }

        switch ($lq_item_branch)
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

        $lq_item_date = date('Y-m-d', strtotime($lq_due_date));
        
        echo "<title>$branch_acro$lq_agreement's Details</title>"
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
                        <h1>Item Details</h1>
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
                                echo "<h2>$branch_acro$lq_agreement's Information</h2>";
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='liquidated_items.php'"><img src="../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created by: <?php echo $lq_creator_name ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Created at: <?php echo $lq_created_at ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Liquidated at: <?php echo $liquidated_at ?> </span>
                                </div>
                            </div>
                            <hr>
                            <form action="" method="POST">
                                <div class="archive_btn_cont">
                                    <?php 
                                        // if($lq_status != "Active" && $a_status != "Overdue")
                                        // {
                                        //     echo "<button type=\"submit\" name=\"submit\"><img src=\"../resources/img/icons/unarchive_w.png\" alt=\"unarchive\">Restore</button>";
                                        // }

                                        // if (isset($_POST['delete'])) 
                                        // {
                                        //     $sql = "DELETE FROM transactions_archive WHERE item_id = ?";
                                        //     $stmt = $conn->prepare($sql);
                                        //     $stmt->bind_param("i", $item_id);
                                        //     if($stmt->execute())
                                        //     {
                                        //         $sql = "DELETE FROM items_archive WHERE item_id = ?";
                                        //         $stmt = $conn->prepare($sql);
                                        //         $stmt->bind_param("i", $item_id);
                                        //         $stmt->execute();

                                        //         $_SESSION['renew_success_msg'] = "Item has been deleted.";

                                        //         header("Location: ../archives/archived_items.php");
                                        //         exit();
                                        //     } 
                                        // }

                                        // if ($role === "admin")
                                        // {
                                        //     echo '<br><hr>
                                        //         <button type="submit" name="delete" onclick="return confirm(\'This process cannot be undone. Are you sure you want to proceed?\\nIt will also delete all transactions related to the item.\');"><img src="../resources/img/icons/delete_forever_w.png" alt="delete">Delete Permanently</button>
                                        //         <div class="archive_text">
                                        //             <span class="message_warning"><img src="../resources/img/icons/warning.png" alt="warning">Deleting this data cannot be undone.</span>
                                        //         </div>';
                                        // } else {
                                        //     echo '<button type="submit" name="req_delete"><img src="../resources/img/icons/reminder_blue.png" alt="request">Request Deletion</button>';
                                        // }
                                    ?>
                                    
                                </div>
                            </form>
                        </div>
                        <div class="details_editable">
                            <form action="" method="POST" class="editable_item_section">
                                <div class="item_info_detail_row">
                                    <label for="agreement_num">Agreement Number</label>
                                    <input type="text" name="agreement_num" id="agreement_num" class="item_tags_disabled" value="<?php echo $lq_agreement; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" name="item_name" id="item_name" class="item_tags" value="<?php echo $lq_item_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" class="item_tags" value="<?php echo $lq_client_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_email">Client Email</label>
                                    <input type="email" name="client_email" id="client_email" class="item_tags" value="<?php echo $lq_email; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_contact">Client Contact Info</label>
                                    <input type="text" name="client_contact" id="client_contact" class="item_tags" value="<?php echo $lq_contact; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="branch">Branch</label>
                                    <input type="text" name="branch" id="branch" class="item_tags" value="<?php echo $lq_item_branch; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="category">Category</label>
                                    <input type="text" name="category" id="category" class="item_tags" value="<?php echo $lq_category; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="due_date">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" class="item_tags" value="<?php echo $lq_item_date; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="principal">Principal</label>
                                    <input type="text" name="principal" id="principal" class="item_tags" value="<?php echo $lq_principal; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="interest">Interest</label>
                                    <input type="text" name="interest" id="interest" class="item_tags" value="<?php echo $lq_interest; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="remarks">Remarks</label>
                                    <input type="text" name="remarks" id="remarks" class="item_tags" value="<?php echo $lq_remarks; ?>" disabled>
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
                            // try
                            // {
                            //     if(isset($_POST['submit']))
                            //     {
                            //         // Check muna si client
                            //         $sql = "SELECT * FROM clients WHERE client_id = ?";
                            //         $stmt = $conn->prepare($sql);
                            //         $stmt->bind_param("i", $a_fetch_c_id);
                            //         $stmt->execute();
                            //         $result = $stmt->get_result();

                            //         if($result->num_rows > 0) // If existing
                            //         {
                            //             $client_exist = true;
                            //         }
                            //         else // kung inde, alisin sya sa archive
                            //         {
                            //             $sql = "INSERT INTO clients (client_id, fullname, contact, email, address, created_at) 
                            //                     VALUES (?, ?, ?, ?, ?, ?)";
                            //             $stmt = $conn->prepare($sql);
                            //             $stmt->bind_param("isisss", $a_fetch_c_id, $a_client_name, $a_contact, $a_email, $a_client_addr, $a_c_create_at);
                            //             if($stmt->execute())
                            //             {
                            //                 $sql = "DELETE FROM clients_archive WHERE client_id = ?";
                            //                 $stmt = $conn->prepare($sql);
                            //                 $stmt->bind_param("i", $a_fetch_c_id);
                            //                 $stmt->execute();
                            //             }
                                        
                            //             $client_exist = true;
                            //         }

                            //         if(isset($client_exist) && $client_exist = true)
                            //         {
                            //             //Check if existing si argeement_num
                            //             $sql = "SELECT * FROM inventory WHERE agreement_num = ? AND branch_id = ?";
                            //             $stmt = $conn->prepare($sql);
                            //             $stmt->bind_param("ii", $a_agreement, $a_fetch_b_id);
                            //             $stmt->execute();
                            //             $result = $stmt->get_result();
                            //             if($result->num_rows > 0) 
                            //             {
                            //                 $_SESSION['error_msg'] = "Agreement number already taken.";

                            //                 header("Location: " . $_SERVER['REQUEST_URI']);
                            //                 exit();
                            //             }
                            //             else
                            //             {
                            //                 //Insert item back to the inventory
                            //                 $sql = "INSERT INTO inventory (item_id, client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_at, updated_at, created_by, updated_by, interest, is_omitted)
                            //                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            //                 $stmt = $conn->prepare($sql);
                            //                 $stmt->bind_param("iiissidsssssssdi", $item_id, $a_fetch_c_id, $a_fetch_b_id, $a_item_name, $a_category, $a_agreement, $a_principal, $a_status, $a_due_date, $a_remarks, $a_item_created, $a_item_updated, $creator_uname, $editor_uname, $a_interest, $a_is_omitted);
                                            
                            //                 if($stmt->execute()) //Remove from archive
                            //                 {
                            //                     $sql = "DELETE FROM items_archive WHERE item_id = ?";
                            //                     $stmt = $conn->prepare($sql);
                            //                     $stmt->bind_param("i", $item_id);
                            //                     $stmt->execute();
                            //                 }

                            //                 //Getting all the transactions from archive
                            //                 $sql = "SELECT transaction_id, agreement_num, item_id, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                            //                         FROM transactions_archive
                            //                         WHERE item_id = ?";
                            //                 $stmt = $conn->prepare($sql);
                            //                 $stmt->bind_param("i", $item_id);
                            //                 $stmt->execute();
                            //                 $result = $stmt->get_result();

                            //                 if($result->num_rows > 0) 
                            //                 {
                            //                     while($row = $result->fetch_assoc())
                            //                     {
                            //                         $transArch_id = htmlspecialchars($row['transaction_id']);
                            //                         $transArch_agreement = htmlspecialchars($row['agreement_num']);
                            //                         $transArch_i_id = htmlspecialchars($row['item_id']);
                            //                         $transArch_c_id = htmlspecialchars($row['client_id']);
                            //                         $transArch_b_id = htmlspecialchars($row['branch_id']);
                            //                         $transArch_amt = htmlspecialchars($row['amount']);
                            //                         $transArch_type = htmlspecialchars($row['type_of_pay']);
                            //                         $transArch_creator = htmlspecialchars($row['created_by']);
                            //                         $transArch_c_at = htmlspecialchars($row['created_at']);
                            //                         $transArch_e_at = htmlspecialchars($row['edited_at']);
                            //                         $transArch_method = htmlspecialchars($row['method']);
                            //                         $transArch_p_date = htmlspecialchars($row['paid_date']);
                            //                         $transArch_is_link = htmlspecialchars($row['is_linked']);

                            //                         $sql = "INSERT INTO transactions (transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked)
                            //                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            //                         $stmt = $conn->prepare($sql);
                            //                         $stmt->bind_param("iiiiidssssssi", $transArch_id, $transArch_agreement, $transArch_c_id, $transArch_b_id, $transArch_i_id, $transArch_amt, $transArch_type, $transArch_creator, $transArch_c_at, $transArch_e_at, $transArch_method, $transArch_p_date, $transArch_is_link);
                            //                         if($stmt->execute())
                            //                         {
                            //                             //Delete sa transac
                            //                             $sql = "DELETE FROM transactions_archive WHERE transaction_id = ?";
                            //                             $stmt = $conn->prepare($sql);
                            //                             $stmt->bind_param("i", $transArch_id);
                            //                             $stmt->execute();
                            //                         }
                            //                     }

                            //                     $transac_count = $result->num_rows;
                            //                     if($transac_count > 0)
                            //                     {
                            //                         $affected_transacs = "Restored number of Transactions: $transac_count";
                            //                     }
                            //                 }

                            //                 $success_str = "Item successfully restored.";
                            //                 if(isset($affected_transacs))
                            //                 {
                            //                     $success_str .= " ($affected_transacs)";
                            //                 }
                                                        
                            //                 $_SESSION['renew_success_msg'] = $success_str;

                            //                 header("Location: ../archives/archived_items.php");
                            //                 exit();
                            //             }
                            //         }                             
                            //     }
                            // }
                            // catch (Throwable $e)
                            // {
                            //     $_SESSION['error_msg'] = $e->getMessage();
                            // }
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