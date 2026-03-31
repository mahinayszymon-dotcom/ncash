<?php
ob_start();
include("../config/session_check.php");  // pang check ng session
include("../config/db_conn.php");   // pang connect sa db
include("../db/branch_fetch.php"); // para kunin ung related sa branch
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/transactions.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/logs.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <style>
        .nav_links ul li:nth-child(4) {
            background-color: transparent !important;
            opacity: 0.85 !important;
        }
        .nav_links ul li:nth-child(4) img {
            opacity: 0.85 !important;
        }

        .nav_links ul li:nth-child(4):hover {
            opacity: 1 !important;
        }
        .nav_links ul li:nth-child(4):hover img {
            opacity: 1 !important;
        }
    </style>
    <?php 
        if (!isset($_SESSION['main_animation_played'])) {
            echo '<link rel="stylesheet" href="../resources/css/pages/dashboard/main_animation.css">';
            $_SESSION['main_animation_played'] = true;
            // unset($_SESSION['main_animation_played']);
        }
    ?>
</head>
<body>
    <main class="dashboard">
        <nav class="navigation_bar">
            <?php
                include('../includes/nav_bar.php')
            ?>
        </nav>
        <section class="main_content">
            <?php
                include('../includes/top_panel.php')
            ?>
            <div class="data_tableC">
                <div class="data_panel_header">
                    <div class="data_panel_name">
                        <div class="icon_normal">
                            <img src="../resources/img/icons/table_w.png" alt="table_icon">
                        </div>
                        <h2>Your Notifications</h2>
                    </div>
                    <div class="data_panel_buttons">
                        <?php
                            $role = $_SESSION['role'];
                            date_default_timezone_set('Asia/Manila');
        
                            $sql = "SELECT i.agreement_num, c.fullname, i.item_name, i.principal, i.due_date, b.branch_name, i.status
                                    FROM inventory AS i
                                    INNER JOIN clients AS c ON i.client_id = c.client_id
                                    INNER JOIN branches AS b ON i.branch_id = b.branch_id";
                                
                            if($role != 'admin')
                            {
                                $sql .= " WHERE i.branch_id = ?";
                            }
        
                            $stmt = $conn->prepare($sql);
        
                            if($role != 'admin')
                            {
                                $stmt->bind_param("i", $branch_id);
                            }
        
                            $stmt->execute();
                                
                            $result = $stmt->get_result();
                        ?>
                        <form action="notifications.php" method="GET">
                            <span class="custom-arrow-sort"><img src="../resources/img/icons/filter.png" alt="filter"></span>
                            <select name="branch" id="branch" onchange="this.form.submit()" class="sort">
                                <?php $selected = $_GET['branch'] ?? 'all';  ?>
                                <option value="default" <?= $selected === 'default' ? 'selected' : '' ?>>Default Sorting</option>
                                <option value="pending" <?= $selected === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="approved" <?= $selected === 'approved' ? 'selected' : '' ?>>Approved</option>
                                <option value="rejected" <?= $selected === 'rejected' ? 'selected' : '' ?>>Rejeceted</option>
                            </select>
                            <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                        </form>
                        <?php
                            /*Where clause*/
                            $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                            $where = [];
                            $orderBy = '';

                            switch ($sorting)
                            {
                                case 'all':
                                case 'default':
                                    $orderBy = "ORDER BY dr.requested_at ASC";
                                    break;
                                case 'pending': 
                                    $where[] = "dr.status = 'Pending'";
                                    $orderBy = "ORDER BY dr.requested_at ASC";
                                    break;
                                case 'approved': 
                                    $where[] = "dr.status = 'Approved'";
                                    $orderBy = "ORDER BY dr.resolved_at ASC";
                                    break;
                                case 'rejected': 
                                    $where[] = "dr.status = 'Rejected'";
                                    $orderBy = "ORDER BY dr.resolved_at ASC";
                                    break;
                                default:
                                    $orderBy = "ORDER BY dr.requested_at ASC";
                                    break;
                            }

                            if($role != 'admin')
                            {
                                $where[] = "dr.requested_by = ?"; //If not admin, only display items from that branch
                            }

                            $where_sql = '';
                            if (!empty($where)) 
                            {
                                // $sql .= " WHERE " . implode(" AND ", $where);
                                $where_sql = " WHERE " . implode(" AND ", $where);
                            }

                            /*Count*/
                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                            $limit = 12;
                            $offset = ($page - 1) * $limit;

                            $sql = "SELECT dr.request_id, dr.table_name, dr.record_id, u.fullname, dr.branch_id, b.branch_name, dr.reason, dr.status, dr.approved_by, dr.requested_at, dr.resolved_at 
                                FROM deletion_request AS dr
                                INNER JOIN users AS u ON dr.requested_by = u.username
                                INNER JOIN branches AS b ON dr.branch_id = b.branch_id
                                $where_sql
                                $orderBy
                                LIMIT $limit OFFSET $offset";

                            $count_sql = "SELECT COUNT(*) AS total
                                FROM deletion_request AS dr
                                INNER JOIN users AS u ON dr.requested_by = u.username
                                INNER JOIN branches AS b ON dr.branch_id = b.branch_id
                                $where_sql";

                            // $sql .= " $orderBy LIMIT $limit OFFSET $offset";

                            $stmt = $conn->prepare($sql);
                            $count_stmt = $conn->prepare($count_sql);

                            if($role != 'admin')
                            {
                                $self_req = $_SESSION['username'];
                                $stmt->bind_param("s", $self_req);
                                $count_stmt->bind_param("s", $self_req);
                            }

                            $stmt->execute();
                            $result = $stmt->get_result();
                            $count_stmt->execute();
                            $count_result = $count_stmt->get_result();
                            $total_row = $count_result ? $count_result->fetch_assoc() : null;
                            $total = $total_row['total'] ?? 0;
                        ?>
                    </div>
                </div>  
                <div class="table_cont">
                    <table id="notifications_table">
                        <tbody>
                        <?php
                            $number = (($page - 1) * $limit) + 1;

                            if($result->num_rows > 0) 
                            {
                                while($dr_row = $result->fetch_assoc())
                                {
                                    $req_id = htmlspecialchars($dr_row['request_id']);
                                    $table_name = htmlspecialchars($dr_row['table_name']);
                                    $record_id = htmlspecialchars($dr_row['record_id']);
                                    $req_by = htmlspecialchars($dr_row['fullname']);
                                    $req_b_id = htmlspecialchars($dr_row['branch_id']);
                                    $req_branch = htmlspecialchars($dr_row['branch_name']);
                                    $req_reason = htmlspecialchars($dr_row['reason']);
                                    $req_status = htmlspecialchars($dr_row['status']);
                                    $req_apprv_by = htmlspecialchars($dr_row['approved_by']);
                                    $requested_at = htmlspecialchars($dr_row['requested_at']);
                                    $resolved_at = htmlspecialchars($dr_row['resolved_at']);

                                    $req_formatted = date("M d, Y | h:i A", strtotime($requested_at));
                                    $rslv_formatted = date("M d, Y | h:i A", strtotime($resolved_at));

                                    if($req_status == 'Pending')
                                    {
                                        $req_sql = "SELECT archived_date, agreement_num, item_id, amount, is_linked FROM $table_name WHERE transaction_id = ?";
                                        $req_stmt = $conn->prepare($req_sql);
                                        $req_stmt->bind_param("i", $record_id);
                                        $req_stmt->execute();
                                        $req_res = $req_stmt->get_result();
                                        if($req_res->num_rows > 0)
                                        {
                                            $req_ta_row = $req_res->fetch_assoc();
                                            $req_ta_arch_date = htmlspecialchars($req_ta_row['archived_date']);
                                            $req_ta_agreement = htmlspecialchars($req_ta_row['agreement_num']);
                                            $req_ta_i_id = htmlspecialchars($req_ta_row['item_id']);
                                            $req_ta_amt = htmlspecialchars($req_ta_row['amount']);
                                            $req_ta_is_linked = htmlspecialchars($req_ta_row['is_linked']);
                                        }

                                        $req_ta_amt_deci = number_format($req_ta_amt, 2);

                                        if($req_ta_is_linked == 0)
                                        {
                                            $req_str = "a transaction with amount: ₱ $req_ta_amt_deci for agreement no. $req_ta_agreement";
                                        }
                                        else 
                                        {
                                            $req_str = "a split transaction with amount: ₱ $req_ta_amt_deci for agreement no. $req_ta_agreement";
                                        }
                                    }

                                    $notif_type = "Deletion Request";
                                    if($req_status == 'Pending')
                                    {
                                        $notif_title = "Request Deletion for Record in Transaction Archives | $req_status";
                                        $notif_cont = " User '$req_by' has made a deletion request for $req_str in $req_branch with reason: '$req_reason'";
                                    }
                                    else 
                                    {
                                        $notif_title = "Request Deletion for Record in Transaction Archives | $req_status";
                                        $notif_cont = " The request by user '$req_by' has been $req_status by $req_apprv_by";
                                    }

                                    if($req_status != "Pending")
                                    {
                                        echo "
                                            <tr>
                                                <td>
                                                    <!--normal notif like na accept na yung request etc.(papakita sa user)-->
                                                    <div class=\"notif_card\">
                                                        <div class=\"notif_card_header\">
                                                            <div class=\"notif_type\">
                                                                <p>$notif_type</p>
                                                            </div>
                                                            <div class=\"notif_timestamp\">
                                                                <p>$rslv_formatted</p>
                                                            </div>
                                                        </div>
                                                        <div class=\"notif_card_description\">
                                                            <h3>$notif_title</h3>
                                                            <p>$notif_cont</p>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>";
                                    }
                                    else if($req_status == "Pending")
                                    {
                                        echo "<tr>
                                                <td>
                                                    <div class=\"notif_card\">
                                                        <div class=\"notif_card_header\">
                                                            <div class=\"notif_type\">
                                                                <p>$notif_type</p>
                                                            </div>
                                                            <div class=\"notif_timestamp\">
                                                                <p>$req_formatted</p>
                                                            </div>
                                                        </div>
                                                        <div class=\"notif_card_description\">
                                                            <h3>$notif_title</h3>
                                                            <p>$notif_cont</p>
                                                        </div>";
                                                        if($role == "admin")
                                                        {
                                                            echo "
                                                                <form method=\"POST\" action=\"\" onsubmit=\"return confirm('Are you sure you want to proceed?');\">
                                                                    <input type=\"hidden\" name=\"request_id\" value=\"$req_id\">
                                                                    
                                                                    <div class=\"notif_card_actions\">
                                                                        <button type=\"submit\" name=\"user_action\" value=\"accept\" id=\"accept\">Accept</button>
                                                                        <button type=\"submit\" name=\"user_action\" value=\"reject\" id=\"reject\">Reject</button>
                                                                    </div>
                                                                </form>";
                                                        }
                                                echo "</div>
                                                </td>
                                            </tr>";
                                    }
                                }
                            }
                            else
                            {
                                echo
                                    "
                                        <tr style='height: auto; border: none; cursor: auto;'>
                                            <td rowspan='5' colspan='7' class='no_records_found'> 
                                                <br>
                                                <img src=\"../resources/img/icons/no_record_big.png\" alt\"no_records_found\">
                                                <h3 style='font-size: 18px;'>No Records Found</h3>
                                                <br>
                                                <p style='font-size: 15px; opacity: 0.85;'>Try searching a different category or create a new data.</p>
                                                <br>
                                            </td>
                                        </tr>
                                    ";
                            }

                            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_action'])) 
                            {
                                $del_id = $_POST['request_id'];
                                $del_action = $_POST['user_action'];
                                
                                $req_new_stat = ($del_action === 'accept') ? 'Approved' : 'Rejected';

                                if($req_new_stat == 'Approved')
                                {
                                    if($req_ta_is_linked == 0)
                                    {
                                        $sql = "DELETE FROM transactions_archive WHERE transaction_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $record_id);
                                        if($stmt->execute())
 						                {
                                            $approver = $_SESSION['username'];
                                            $curDate = new DateTime();
                                            $current = $curDate->format('Y-m-d H:i:s');

                                            $sql = "UPDATE deletion_request 
                                                    SET status = ?, approved_by = ?, resolved_at = ?
                                                    WHERE request_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("sssi", $req_new_stat, $approver, $current, $del_id);
                                            $stmt->execute();

                                            $audit_u_id = $_SESSION['user_id'];
                                            $audit_action = "Deleted";
                                            $audit_obj = "Archived Transaction";
                                            $audit_desc = "Approved a deletion requestion for archived transaction for agreement no. $req_ta_agreement";

                                            $au_sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                            $au_stmt = $conn->prepare($au_sql);
                                            $au_stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $req_b_id, $current);
 						                    if($au_stmt->execute())
						                    {
                                            	$_SESSION['action_success_msg'] = "Deletion request has been successfully approved.";

                                            	header("Location: " . $_SERVER['REQUEST_URI']);
                                            	exit(); 
						                    }
						                }
                                    }
                                    else if($req_ta_is_linked == 1)
                                    {
                                        $sql = "DELETE FROM transactions_archive WHERE item_id = ? AND archived_date = ? AND is_linked = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("isi", $req_ta_i_id, $req_ta_arch_date, $req_ta_is_linked);
                                        if($stmt->execute())
                                        {
                                            $approver = $_SESSION['username'];
                                            $curDate = new DateTime();
                                            $current = $curDate->format('Y-m-d H:i:s');

                                            $sql = "UPDATE deletion_request 
                                                    SET status = ?, approved_by = ?, resolved_at = ?
                                                    WHERE request_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("sssi", $req_new_stat, $approver, $current, $del_id);
                                            $stmt->execute();

                                            $audit_u_id = $_SESSION['user_id'];
                                            $audit_action = "Deleted";
                                            $audit_obj = "Archived Transaction";
                                            $audit_desc = "Approved a deletion requestion for archived split transaction for agreement no. $req_ta_agreement";

                                            $au_sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                            $au_stmt = $conn->prepare($au_sql);
                                            $au_stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $req_b_id, $current);
                                            if($stmt->execute())
                                            {
                                                $_SESSION['action_success_msg'] = "Deletion request has been successfully approved.";

                                                header("Location: " . $_SERVER['REQUEST_URI']);
                                                exit(); 
                                            }
                                        }
                                    }
                                }
                                else if($req_new_stat == 'Rejected')
                                {
                                    $approver = $_SESSION['username'];
                                    $curDate = new DateTime();
                                    $current = $curDate->format('Y-m-d H:i:s');

                                    $sql = "UPDATE deletion_request 
                                            SET status = ?, approved_by = ?, resolved_at = ?
                                            WHERE request_id = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("sssi", $req_new_stat, $approver, $current, $del_id);
                                    if ($stmt->execute()) 
                                    {
                                        $_SESSION['action_success_msg'] = "Deletion request has been successfully rejected.";

                                        header("Location: " . $_SERVER['REQUEST_URI']);
                                        exit();
                                    } 
                                }
                            }
                        ?>
                            
                        </tbody>
                    </table>
                </div>
                <div class="data_table_actions">
                    <div class="data_table_actions_components">
                        <p>
                            <?php 
                                $shown = $result->num_rows;
                                $start = $offset + 1; 
                                $end = $offset + $shown; 

                                if ($total == 0 || $end == 0) {
                                    echo "Showing 0 entries";
                                } else {
                                    echo "Showing $start – $end of $total entries";
                                }
                            ?>
                        </p>
                    </div>
                    <div class="data_table_actions_components">
                        <div class="pagination">
                            <?php
                                $total_pages = ceil($total / $limit);

                                // Previous link
                                if ($page > 1) {
                                    $prev = $page - 1;
                                    echo "<div class=\"page_button_direct\"><a href='?page=$prev&branch=$sorting'><</a></div>";
                                }

                                // Page number links
                                for ($i = 1; $i <= $total_pages; $i++) {
                                    if ($i == $page) {
                                        echo "<div class=\"page_button_active\">$i</div>"; // current page highlighted
                                    } else {
                                        echo "<div class=\"page_button\"><a href='?page=$i&branch=$sorting'>$i</a></div>";
                                    }
                                }

                                // Next link
                                if ($page < $total_pages) {
                                    $next = $page + 1;
                                    echo "<div class=\"page_button_direct\"><a href='?page=$next&branch=$sorting'>></a></div>";
                                }
                            ?>
                        </div>
                    </div>
                    <div class="data_table_actions_components">
                        <div class="data_actions">          
                            <!--Supposedly dapat naka disable eto. kapag nag check ako sa ilang checkbox, tsaka lang sya mag enable. Dito na ren si multiple selection-->
                            <!-- <button><img src="../resources/img/icons/edit.png" alt="edit"><p>Edit</p></button>
                            <button><img src="../resources/img/icons/archive.png" alt="archive"><p>Archive</p></button> -->
                            <button onclick="window.location.href='../dashboard/transactions.php'"><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                            <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <div class="result_cont_bar">
        <?php
            //$_SESSION['archive_success_msg'] = 'Test';

            if (isset($_SESSION['action_success_msg'])) {
                echo "<span id=\"action_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['action_success_msg'] . "</span>";
    
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('action_success');
                        if (element) {
                            // Use CSS opacity/transition for a smooth fade out (optional)
                            element.style.transition = 'opacity 0.5s ease-out';
                            element.style.opacity = '0';

                            // Remove the element completely after the fade out is complete
                            setTimeout(function() {
                                element.style.display = 'none';
                                // Or remove it from the DOM entirely:
                                // element.parentNode.removeChild(element);
                            }, 500); // 500ms should match your CSS transition time if you add one
                        }
                    }

                    // Call the hideMessage function after 3000 milliseconds (3 seconds)
                    setTimeout(hideMessage, 5000);
                </script>
                ";

                unset($_SESSION['action_success_msg']);
            } else {
                unset($_SESSION['action_success_msg']);
            }
        ?>
    </div>
</body>
</html>