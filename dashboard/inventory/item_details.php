<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
include("../../db/branch_fetch.php");

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
            $item_id = htmlspecialchars($_GET['id']);                                    
        }
        else 
        {
            header("Location: ../../auth/denied.php");
            exit();
        }

        $sql = "SELECT i.agreement_num, c.client_id, c.fullname, c.contact, c.email, c.address, c.created_at AS client_date, i.item_name, i.principal, b.branch_id, b.branch_name, i.category, i.interest, i.status, i.due_date, i.remarks, i.created_at, i.updated_at, i.is_omitted
                FROM inventory AS i
                INNER JOIN clients AS c ON i.client_id = c.client_id
                INNER JOIN branches AS b ON i.branch_id = b.branch_id
                WHERE i.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $agreement_num = htmlspecialchars($row['agreement_num']);
            $fetch_c_id = htmlspecialchars($row['client_id']);
            $client_name = htmlspecialchars($row['fullname']);
            $contact = htmlspecialchars($row['contact']);
            $email = htmlspecialchars($row['email']);
            $client_addr = htmlspecialchars($row['address']);
            $item_name = htmlspecialchars($row['item_name']);
            $c_create_at = htmlspecialchars($row['client_date']);
            $principal = htmlspecialchars($row['principal']);
            $fetch_b_id = htmlspecialchars($row['branch_id']);
            $item_branch = htmlspecialchars($row['branch_name']);
            $category = htmlspecialchars($row['category']);
            $interest = htmlspecialchars($row['interest']);
            $status = htmlspecialchars($row['status']);
            $due_date = htmlspecialchars($row['due_date']);
            $remarks = htmlspecialchars($row['remarks']);
            $item_created = htmlspecialchars($row['created_at']);
            $item_updated = htmlspecialchars($row['updated_at']);
            $is_omit = htmlspecialchars($row['is_omitted']);
        }

        $audit_u_id = $_SESSION['user_id'];
        $audit_action = "Accessed";
        $audit_obj = "Item";
        $audit_desc = "Accessed item '$item_name' with agreement no. $agreement_num on inventory";

        $curDate = new DateTime();
        $current = $curDate->format('Y-m-d H:i:s');

        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $fetch_b_id, $current);
        $stmt->execute();  

        $createDate = new DateTime($item_created);
        $created_at = $createDate->format("F j, Y");
        $updDate = new DateTime($item_updated);
        $updated_at = $updDate->format("F j, Y");

        $sql = "SELECT u.fullname AS creator, u.username FROM inventory AS i
                INNER JOIN users AS u ON i.created_by = u.username
                WHERE i.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $creator_name = htmlspecialchars($row['creator']);
            $creator_uname = htmlspecialchars($row['username']);
        }

        $sql = "SELECT u.fullname AS editor, u.username FROM inventory AS i
                INNER JOIN users AS u ON i.updated_by = u.username
                WHERE i.item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $editor_name = htmlspecialchars($row['editor']);
            $editor_uname = htmlspecialchars($row['username']);
        }

        switch ($item_branch)
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

        $principal = (int)$principal;
        $principal_int = intval($principal);
        $rate = ($interest / $principal) * 100;
        $item_date = date('Y-m-d', strtotime($due_date));
        
        echo "<title>$branch_acro$agreement_num's Details</title>"
    ?>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/inventory.css">
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
                        <h1>Item Details</h1>
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
                                echo "<h2>$branch_acro$agreement_num's Information</h2>";
                            ?>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../inventory.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_details_cont">
                        <div class="details_info">
                            <div class="row_cont">
                                <?php
                                    $status_style = "font-size: 15px;";
                                    
                                    if ($status == 'Active') {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400; padding: 5px 8px; border-radius: 5px; background-color: #d2e8ce; color: #739667;";
                                    } else if ($status == 'Redeemed') {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #d1d8ed; color: #6c799d;";
                                    } else if ($status == "Overdue") {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #f8dfbf; color: #b68b53;";
                                    } else {
                                        $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #f1eceb; color: #a6a094;";
                                    }

                                    echo '<span style="' . $status_style . '">This item is ' . $status . '</span>';
                                ?>
                            </div>
                            <div class="creator_cont">
                                <div class="row_cont">
                                    <span>Created by: <?php echo $creator_name ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Created at: <?php echo $created_at ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Last edited by: <?php echo $editor_name ?> </span>
                                </div>
                                <div class="row_cont">
                                    <span>Last edited at: <?php echo $updated_at ?> </span>
                                </div>
                            </div>
                            <hr>
                            <?php 
                            // if($status == "Overdue")
                            // {
                            //     echo "
                            //     <div class=\"liquidate_btn_cont\">
                            //         <button type=\"submit\" name=\"submit\" id=\"liquidate_button\"><img src=\"../../resources/img/icons/archive_w.png\" alt=\"archive\">Hold Item for Liquidation</button>
                            //     </div>
                            //     <br>
                            //     <hr>";
                            // }
                            
                            if($status == "Redeem")
                            {
                                if($is_readonly == 0)
                                {
                                    echo "
                                    <div class=\"archive_btn_cont\">
                                        <button type=\"submit\" name=\"submit\" id=\"archive_button\"><img src=\"../../resources/img/icons/archive_w.png\" alt=\"archive\">Archive this item</button>
                                        <div class=\"archive_text\">
                                            <span class=\"message_info\"><img src=\"../../resources/img/icons/info.png\" alt=\"info\">Archiving this item will move it to a separate list and hide it from active view in this module.</span>
                                        </div>
                                    </div>";
                                }
                            }
                            ?>
                            
                        </div>
                        <?php
                            try
                            {
                                if(isset($_POST['submit']))
                                {
                                    $upd_agreement = htmlspecialchars($_POST['agreement_num']);
                                    $upd_principal = trim(htmlspecialchars($_POST['principal']));
                                    $rate_input = trim(htmlspecialchars($_POST['interest']));
                                    $upd_client_name = trim(ucwords(htmlspecialchars($_POST['client_name'])));
                                    $upd_contact = trim(htmlspecialchars($_POST['client_contact']));
                                    $upd_email = trim(htmlspecialchars($_POST['client_email']));
                                    $upd_address = trim(htmlspecialchars($_POST['client_address']));
                                    $upd_item_name = trim(htmlspecialchars($_POST['item_name']));
                                    $upd_remarks = trim(htmlspecialchars($_POST['remarks']));
                                    
                                    if($role === "admin")
                                    {
                                        $item_b_id = htmlspecialchars($_POST['branch_select']);
                                    }
                                    else 
                                    {
                                        $item_b_id = $branch_id; 
                                    }

                                    if($upd_agreement <= 0)
                                    {
                                        $_SESSION['error_msg'] = "Please input a number greater than 0.";
                                    }
                                    else 
                                    {
                                        $editor = $_SESSION['username'];
                                        
                                        if(empty($upd_principal) || empty($rate_input) || empty($upd_client_name) || empty($upd_contact) || empty($upd_email) || empty($upd_address) || empty($upd_item_name) || empty($upd_remarks))
                                        {
                                            $_SESSION['error_msg'] = "Please fill out all input fields.";
                                        }
                                        else 
                                        {
                                            if($upd_principal > 0)
                                            {
                                                if($rate_input > 0)
                                                {
                                                    $sql = "SELECT * FROM inventory WHERE agreement_num = ? AND branch_id = ? AND item_id != ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("iii", $upd_agreement, $item_b_id, $item_id);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();

                                                    if($result->num_rows > 0)
                                                    {
                                                        $_SESSION['error_msg'] =  "Agreement Number already exists. Please enter a different value.";
                                                    }
                                                    else 
                                                    {
                                                        $sql = "UPDATE clients
                                                                SET fullname = ?, contact = ?, email = ?, address = ?
                                                                WHERE client_id = ?";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("sissi", $upd_client_name, $upd_contact, $upd_email, $upd_address, $fetch_c_id);
                                                            
                                                        if($stmt->execute())
                                                        {
                                                            $client_id = $fetch_c_id;
                                                            $client_count = $stmt->affected_rows;
                                                        }
                                                        else 
                                                        {
                                                            $_SESSION['error_msg'] = "Error occurred while updating item info. (Client Info)";
                                                        }

                                                        if(isset($client_id))
                                                        {
                                                            
                                                            $upd_category = htmlspecialchars($_POST['category']);
                                                            $upd_due = htmlspecialchars($_POST['due_date']);
                                                            $new_rate = $rate_input / 100;
                                                            $upd_interest = $upd_principal * $new_rate;

                                                            $inputDate = new DateTime($upd_due);
                                                            $curDate = new DateTime();
                                                            if($status != "Redeemed")
                                                            {
                                                                if($inputDate < $curDate)
                                                                {
                                                                    $status = "Overdue";
                                                                }
                                                                else
                                                                {
                                                                    $status = "Active";
                                                                }
                                                            }
                                                            
                                                            $current = $curDate->format('Y-m-d H:i:s');

                                                            $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("i", $item_b_id);
                                                            $stmt->execute();
                                                            $result = $stmt->get_result();
                                                            $row = $result->fetch_assoc();

                                                            $fetch_eb = (float)htmlspecialchars($row['end_balance']);
                                                            $upd_success = false;

                                                            if(isset($fetch_eb) && (int)$is_omit == 0)
                                                            {
                                                                $prev_eb_val = ($fetch_eb + (float)$principal_int) - (float)$interest; //reverse the old
                                                                $new_eb_val = ($prev_eb_val - (float)$upd_principal) + (float)$upd_interest; //new advance interest
                                                                $sql = "UPDATE branches
                                                                        SET end_balance = ?
                                                                        WHERE branch_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("di", $new_eb_val, $branch_id);
                                                                if($stmt->execute())
                                                                {
                                                                    $upd_success = true;
                                                                }
                                                            }
                                                            else 
                                                            {
                                                                $upd_success = true;
                                                            }

                                                            if($upd_success)
                                                            {
                                                                $sql = "UPDATE inventory
                                                                        SET client_id = ?, branch_id = ?, item_name = ?, category = ?, agreement_num = ?, principal = ?, status = ?, due_date = ?, remarks = ?, interest = ?
                                                                        WHERE item_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("iissidsssdi", $client_id, $item_b_id, $upd_item_name, $upd_category, $upd_agreement, $upd_principal, $status, $upd_due, $upd_remarks, $upd_interest, $item_id);
                                                                if($stmt->execute())
                                                                {
                                                                    $insert_count = $stmt->affected_rows;
                                                                }
                                                                else 
                                                                {
                                                                    $_SESSION['error_msg'] = "Error occurred while updating item info. (Client Info)";
                                                                }
                                                            }
                                                            else
                                                            {
                                                                $_SESSION['error_msg'] = "Error occurred while updating item info.";
                                                            }
                                                        }
                                                        else 
                                                        {
                                                            $_SESSION['error_msg'] = "Error occurred while updating item info.";
                                                        }

                                                        if(isset($insert_count) && isset($client_count))
                                                        {
                                                            if($insert_count > 0 || $client_count > 0)
                                                            {
                                                                $sql = "UPDATE inventory
                                                                        SET updated_at = ?, updated_by = ?
                                                                        WHERE item_id = ?";
                                                                $stmt = $conn->prepare($sql);
                                                                $stmt->bind_param("ssi", $current, $editor, $item_id);
                                                                if($stmt->execute())
                                                                {
                                                                    $sql = "UPDATE transactions
                                                                            SET agreement_num = ?, edited_at = ?
                                                                            WHERE item_id = ?";
                                                                    $stmt = $conn->prepare($sql);
                                                                    $stmt->bind_param("isi", $upd_agreement, $current, $item_id);
                                                                    if($stmt->execute())
                                                                    {
                                                                        $sql = "UPDATE transactions_archive
                                                                            SET agreement_num = ?, edited_at = ?
                                                                            WHERE item_id = ?";
                                                                        $stmt = $conn->prepare($sql);
                                                                        $stmt->bind_param("isi", $upd_agreement, $current, $item_id);
                                                                        if($stmt->execute())
                                                                        {
                                                                            $audit_u_id = $_SESSION['user_id'];
                                                                            $audit_action = "Edited";
                                                                            $audit_obj = "Item";
                                                                            $audit_desc = "Edited item '$item_name' with agreement no. $upd_agreement on inventory";

                                                                            $curDate = new DateTime();
                                                                            $current = $curDate->format('Y-m-d H:i:s');

                                                                            $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                                                            $stmt = $conn->prepare($sql);
                                                                            $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $fetch_b_id, $current);
                                                                            if($stmt->execute())
                                                                            {
                                                                                $_SESSION['change_success_msg'] = "Item Successfully Updated!";
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                                else
                                                                {
                                                                    $_SESSION['error_msg'] = "An error has occurred while updating the item information.";
                                                                }
                                                            }
                                                            else 
                                                            {
                                                                $_SESSION['nochange_msg'] = "No Changes made.";
                                                            }
                                                        }
                                                    }
                                                }
                                                else
                                                {
                                                    $_SESSION['error_msg'] = "New Interest Rate should be greater than 0.";
                                                }
                                            }
                                            else 
                                            {
                                                $_SESSION['error_msg'] = "New Principal should be greater than 0.";
                                            }
                                        }
                                    }
                                }
                            }
                            catch (Throwable $e)
                            {
                                $_SESSION['error_msg'] = $e->getMessage();
                            }
                        ?>  
                        <div class="details_editable">
                            <form action="" method="POST" class="editable_item_section">
                                <div class="item_info_detail_row">
                                    <label for="agreement_num">Agreement Number</label>
                                    <input type="text" name="agreement_num" id="agreement_num" class="item_tags" pattern="[0-9]*" value="<?php echo $agreement_num; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" name="item_name" id="item_name" class="item_tags" value="<?php echo $item_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" class="item_tags" pattern="[A-Za-z ]+" value="<?php echo $client_name; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_email">Client Email</label>
                                    <input type="email" name="client_email" id="client_email" class="item_tags" value="<?php echo $email; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_contact">Client Contact Info</label>
                                    <input type="text" name="client_contact" id="client_contact" class="item_tags" pattern="[9]{1}[0-9]{2}[0-9]{3}[0-9]{4}" value="<?php echo $contact; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="client_address">Client Address</label>
                                    <input type="text" name="client_address" id="client_address" class="item_tags" value="<?php echo $client_addr; ?>" disabled>
                                </div>
                                <?php
                                    if ($role === "admin") {
                                        echo    '<div class="item_info_detail_row">
                                                    <label for="branch_select">Branch</label>
                                                    <select name="branch_select" id="branch_select" class="item_tags" required onchange="updateAgreement()" disabled>
                                                        <option value="" disabled>--Select Branch--</option>
                                                        <option value="1100"'; echo ($item_branch == "Marikina-Pasig") ? "selected" : ""; echo '>Marikina-Pasig Branch</option>
                                                        <option value="1101"'; echo ($item_branch == "Quezon City") ? "selected" : ""; echo '>Quezon City Branch</option>
                                                        <option value="1102"'; echo ($item_branch == "Makati") ? "selected" : ""; echo '>Makati Branch</option>
                                                    </select>
                                                </div>';
                                    }
                                ?>
                                <div class="item_info_detail_row">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" class="item_tags" required disabled>
                                        <option value="" disabled selected>--Select Category--</option>
                                        <option value="Personal Accessories" <?php echo ($category == "Personal Accessories") ? "selected" : ""; ?>>Personal Accessories</option>
                                        <option value="Electronic Gadgets" <?php echo ($category == "Electronic Gadgets") ? "selected" : ""; ?>>Electronic Gadgets</option>
                                        <option value="Vehicles" <?php echo ($category == "Vehicles") ? "selected" : ""; ?>>Vehicles</option>
                                        <option value="Real Estate Property" <?php echo ($category == "Real Estate Property") ? "selected" : ""; ?>>Real Estate Property</option>
                                    </select>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="due_date">Due Date</label>
                                    <input type="date" name="due_date" id="due_date" class="item_tags" value="<?php echo $item_date; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="principal">Principal</label>
                                    <input type="text" name="principal" id="principal" pattern="[0-9]*" class="item_tags" value="<?php echo $principal_int; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="interest">Interest Rate (%) Current Interest: <?php echo $interest; ?></label>
                                    <input type="text" name="interest" id="interest" pattern="[0-9]*" class="item_tags" value="<?php echo $rate; ?>" disabled>
                                </div>
                                <div class="item_info_detail_row">
                                    <label for="remarks">Remarks</label>
                                    <input type="text" name="remarks" id="remarks" class="item_tags" value="<?php echo $remarks; ?>" disabled>
                                </div>
                                <?php 
                                    if ($role === "admin") {
                                        echo '<div class="item_info_detail_row">
                                                
                                            </div>';
                                    }

                                    if ($status != "Redeemed")
                                    {
                                        if($is_readonly == 0)
                                        {
                                            echo '<div class="item_info_detail_btn">
                                                    <button type="button">Edit</button>
                                                    <button type="submit" id="submit" name="submit" disabled>Save Changes</button>
                                                </div>';
                                        }     
                                    }
                                ?>
                            </form>
                            <div class="result_cont">
                                <?php
                                    // $_SESSION['change_success_msg'] = '';

                                    if (isset($_SESSION['change_success_msg'])) {
                                        $redirect_url = "../../dashboard/inventory.php";
                                        $delay = 0; // three seconds muna taymperst

                                        // echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
                                        
                                        echo "<meta http-equiv='refresh' content='" . $delay . "; url=" . $redirect_url . "'>";

                                        // unset($_SESSION['change_success_msg']); 
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
                        <?php
                            if($is_readonly == 0)
                            {
                                echo "<div class=\"modal-actions\">
                                          <button type=\"submit\" id=\"proceed\" name=\"proceed\" class=\"btn-proceed\"><img src=\"../../resources/img/icons/arrow_circle_right.png\" alt=\"proceed\">Proceed to Archive</button>
                                      </div>";
                            }
                        ?>
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
                            // Check muna si agreement num sa archive
                            $check_sql = "SELECT * FROM items_archive WHERE agreement_num = ? AND branch_id = ?";
                            $stmt = $conn->prepare($check_sql);
                            $stmt->bind_param("ii",  $agreement_num, $fetch_b_id);
                            $stmt->execute();
                            $check_result = $stmt->get_result();

                            if($check_result->num_rows == 0)
                            {
                                $archiver = $_SESSION['username'];
                                $success_str = "Item successfully archived!";

                                // Transacs first
                                $sql = "SELECT transaction_id, agreement_num, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                                        FROM transactions
                                        WHERE item_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $item_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if($result->num_rows > 0) 
                                {
                                    while($row = $result->fetch_assoc())
                                    {
                                        $transac_id = htmlspecialchars($row['transaction_id']);
                                        $transac_agreement = htmlspecialchars($row['agreement_num']);
                                        $transac_c_id = htmlspecialchars($row['client_id']);
                                        $transac_b_id = htmlspecialchars($row['branch_id']);
                                        $transac_amt = htmlspecialchars($row['amount']);
                                        $transac_type = htmlspecialchars($row['type_of_pay']);
                                        $transac_creator = htmlspecialchars($row['created_by']);
                                        $transac_c_at = htmlspecialchars($row['created_at']);
                                        $transac_e_at = htmlspecialchars($row['edited_at']);
                                        $transac_method = htmlspecialchars($row['method']);
                                        $transac_p_date = htmlspecialchars($row['paid_date']);
                                        $transac_is_link = htmlspecialchars($row['is_linked']);

                                        //insert si transac sa archive
                                        $transac_archiver = "system";
                                        $transac_reason = "Joined archive when item was archived";

                                        $sql = "INSERT INTO transactions_archive (archived_by, archived_date, transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked, reason)
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("ssiiiiidssssssis", $transac_archiver, $current, $transac_id, $transac_agreement, $transac_c_id, $transac_b_id, $item_id, $transac_amt, $transac_type, $transac_creator, $transac_c_at, $transac_e_at, $transac_method, $transac_p_date, $transac_is_link, $transac_reason);
                                        if($stmt->execute())
                                        {
                                            //Delete sa transac
                                            $sql = "DELETE FROM transactions WHERE transaction_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $transac_id);
                                            $stmt->execute();
                                        }
                                    }

                                    $transac_count = $result->num_rows;
                                    if($transac_count > 0)
                                    {
                                        $affected_transacs = "Affected number of Transactions: $transac_count";
                                    }
                                }

                                if(isset($_POST['custom_reason']) && !empty(trim($_POST['custom_reason'])))
                                {
                                    $archive_reason = htmlspecialchars($_POST['custom_reason']);
                                }
                                else if(isset($_POST['reason']))
                                {
                                    $archive_reason = htmlspecialchars($_POST['reason']);
                                }

                                //Insert na sa archive
                                $sql = "INSERT INTO items_archive (archived_by, item_id, client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_at, updated_at, created_by, updated_by, interest, is_omitted, reason) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("siiissidsssssssdis", $archiver, $item_id, $fetch_c_id, $fetch_b_id, $item_name, $category, $agreement_num, $principal, $status, $due_date, $remarks, $item_created, $item_updated, $creator_uname, $editor_uname, $interest, $is_omit, $archive_reason);
                                if($stmt->execute())
                                {
                                    //Delete sa inventory
                                    $sql = "DELETE FROM inventory WHERE item_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $item_id);
                                    $stmt->execute();
                                }

                                //check if client still has records in inventory (if wala, archive narin si client)
                                $sql = "SELECT * FROM inventory WHERE client_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $fetch_c_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                //if wala nang item si client
                                if($result->num_rows == 0)
                                {
                                    $client_archiver = "system";
                                    $client_reason = "No more items in the inventory";
                                    //archive na sya
                                    $sql = "INSERT INTO clients_archive (archived_by, client_id, fullname, contact, email, address, created_at, reason)
                                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("sisissss", $client_archiver, $fetch_c_id, $client_name, $contact, $email, $client_addr, $c_create_at, $client_reason);
                                    if($stmt->execute())
                                    {
                                        $sql = "DELETE FROM clients WHERE client_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $fetch_c_id);
                                        $stmt->execute();
                                    }
                                }

                                if(isset($affected_transacs))
                                {
                                    $success_str .= " ($affected_transacs)";
                                }
                                
                                $_SESSION['archive_success_msg'] = "$success_str";

                                header("Location: ../../dashboard/inventory.php");
                                exit();
                            }
                            else 
                            {
                                $_SESSION['error_msg'] = "Agreement number is already in archive. Please replace first before archiving.";

                                header("Location: " . $_SERVER['REQUEST_URI']);
                                exit(); 
                            } 
                        } 
                        else 
                        {
                            $_SESSION['error_msg'] = "Please choose or enter the reason for archiving the item.";

                            header("Location: " . $_SERVER['REQUEST_URI']);
                            exit(); 
                        }
                    }
                ?>
            </div>        
        </div>
    </div>
    <script src="../../resources/js/fetch_agreement.js"></script>
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