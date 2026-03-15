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
    <title>Audit End Balance</title>
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
                        <h1>End Balance</h1>
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
                                <img src="../../resources/img/icons/end_bal.png" alt="end_bal">
                            </div>
                            <h2>Audit End Balance</h2>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../transactions.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    
                    <div class="end_bal_cont">
                        <div class="end_bal">
                            <?php
                                if($role === "admin")
                                {
                                    $select_br = isset($_REQUEST['eb_branch']) ? (int)$_REQUEST['eb_branch'] : 1100;
                                }
                                else
                                {
                                    $select_br = $_SESSION['branch_id'];
                                }

                                $sql = "SELECT start_balance FROM branches WHERE branch_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("i", $select_br);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();

                                $branch_sb = number_format(($row['start_balance'] ?? 0), 2);   
                            ?>
                            <div class="end_bal_amount">
                                <div class="eb_title">
                                    <p>STARTING BALANCE</p>
                                </div>
                                <div class="eb_value_cont">
                                    <div class="eb_value">
                                        <p>₱ <?php echo $branch_sb; ?> </p>
                                    </div>
                                    <div class="eb_action">
                                        <form action="" method="POST" id="branchForm">
                                            <?php               
                                                if($role === "admin")
                                                {
                                                    echo '<div class="form_conts">
                                                        <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                        <select name="eb_branch" id="eb_branch" onchange="document.getElementById(\'branchForm\').submit()" class="sort2">
                                                            
                                                            <option value="1100" ' . ($select_br == 1100 ? "selected" : "") . '>Pasig Branch</option>
                                                            
                                                            <option value="1101" ' . ($select_br == 1101 ? "selected" : "") . '>Quezon City Branch</option>
                                                            
                                                            <option value="1102" ' . ($select_br == 1102 ? "selected" : "") . '>Makati City Branch</option>
                                                            
                                                        </select>
                                                        <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                                    </div>';
                                                }
                                            ?>
                                            <div class="form_conts">
                                                <button type="submit" name="proceed" id="edit_btn"><img src="../../resources/img/icons/edit_w_s.png" alt="edit"></button>
                                            </div>
                                        </form>              
                                    </div>
                                </div>
                            </div>
                            <div class="quick_transac">
                                <div class="eb_title">
                                    <p>QUICK TRANSACTION</p>
                                </div>
                                <div class="eb_transac_cont">
                                    <div class="eb_input">
                                        <form action="" method="POST">
                                            <div class="form_conts">
                                                <input type="text" name="ebt_label" id="ebt_label" placeholder="Name your transaction" required>
                                            </div>
                                            <div class="form_conts">
                                                <input type="number" name="ebt_amount" id="ebt_amount" step="any" placeholder="Amount" required>
                                            </div>
                                            <div class="form_conts">
                                                <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                <select name="ebt_type" id="ebt_type" class="sort3" required>
                                                    <option value="Debit" selected>Debit</option>
                                                    <option value="Credit">Credit</option>   
                                                </select>
                                                <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                            </div>
                                            <?php
                                                if($role === "admin")
                                                    {
                                                        echo '<div class="form_conts">
                                                            <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                            <select name="ebt_branch" id="ebt_branch" class="sort2">
                                                                
                                                                <option value="1100" ' . '>Pasig Branch</option>
                                                                
                                                                <option value="1101" ' . '>Quezon City Branch</option>
                                                                
                                                                <option value="1102" ' . '>Makati City Branch</option>
                                                                
                                                            </select>
                                                            <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                                        </div>';
                                                    }
                                            ?>
                                            <div class="form_conts">
                                                <button type="submit" name="add_ebt" id="add_ebt"><img src="../../resources/img/icons/add_w_s.png" alt="add"></button>
                                            </div>
                                        </form>  
                                        <?php
                                            if(isset($_POST['add_ebt']))
                                            {
                                                $inp_ebt_amount = htmlspecialchars($_POST['ebt_amount']);
                                                if($role === "admin")
                                                {
                                                    $ebt_b_id = htmlspecialchars($_POST['ebt_branch']);
                                                }
                                                else 
                                                {
                                                    $ebt_b_id = $_SESSION['branch_id'];
                                                }

                                                if (filter_var($inp_ebt_amount, FILTER_VALIDATE_FLOAT) !== false)
                                                {
                                                    $ebt_amount = round((float)$inp_ebt_amount, 2);
                                                    $ebt_label = htmlspecialchars($_POST['ebt_label']);
                                                    $ebt_type = htmlspecialchars($_POST['ebt_type']);
                                                    $ebt_creator = $_SESSION['username'];

                                                    $curDate = new DateTime();
                                                    $ebt_c_date = $curDate->format('Y-m-d H:i:s'); 
                                                    $upd_success = false;

                                                    $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $ebt_b_id);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();

                                                    $branch_eb = (float)htmlspecialchars($row['end_balance']);

                                                    $sql = "UPDATE branches
                                                                SET end_balance = ?
                                                                WHERE branch_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    if($ebt_type == "Debit")
                                                    {
                                                        $new_val = $branch_eb - $ebt_amount;
                                                        $stmt->bind_param("di", $new_val, $ebt_b_id);
                                                        if($stmt->execute())
                                                        {
                                                            $upd_success = true;
                                                        }
                                                    }
                                                    else if($ebt_type == "Credit")
                                                    {
                                                        $new_val = $branch_eb + $ebt_amount;
                                                        $stmt->bind_param("di", $new_val, $ebt_b_id);
                                                        if($stmt->execute())
                                                        {
                                                            $upd_success = true;
                                                        }
                                                    }

                                                    if($upd_success)
                                                    {
                                                        $sql = "INSERT INTO eb_transactions (branch_id, label, amount, type_of_transac, created_by, created_at)
                                                                VALUES (?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("isdsss", $ebt_b_id, $ebt_label, $ebt_amount, $ebt_type, $ebt_creator, $ebt_c_date);
                                                        if($stmt->execute())
                                                        {
                                                            $audit_u_id = $_SESSION['user_id'];
                                                            $audit_action = "Created";
                                                            $audit_obj = "EB Transaction";
                                                            $audit_desc = "Created end bal transaction with amount '$ebt_amount'";

                                                            $curDate = new DateTime();
                                                            $current = $curDate->format('Y-m-d H:i:s');

                                                            $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $ebt_b_id, $current);
                                                            if($stmt->execute())
                                                            { 
                                                                $_SESSION['update_eb_success_msg'] = "End Balance Transaction was added successfully!";
                                                                header('Location: ' . $_SERVER['PHP_SELF']);
                                                                exit();
                                                            }
                                                        }
                                                        else 
                                                        {
                                                            //Error msg dito
                                                        }
                                                    }
                                                }
                                            }
                                        ?>            
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="end_bal_table">
                            <div class="table_cont">
                                <table id="audit">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Label</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            /*Count*/
                                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                            $limit = 12;
                                            $offset = ($page - 1) * $limit;
    
                                            $total = $total_row['total'] ?? 0;

                                            $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                            $where = [];
                                            $orderBy = '';
                                            $where_sql = '';

                                            switch ($sorting)
                                            {
                                                case 'all':
                                                    $orderBy = " ORDER BY eb.created_at DESC";
                                                    break;
                                                case 'pasig':
                                                    $where[] = "b.branch_id = 1100";
                                                    $orderBy = " ORDER BY eb.created_at DESC";
                                                    break;
                                                case 'quezon':
                                                    $where[] = "b.branch_id = 1101";
                                                    $orderBy = " ORDER BY eb.created_at DESC";
                                                    break;
                                                case 'makati': 
                                                    $where[] = "b.branch_id = 1102";
                                                    $orderBy = " ORDER BY eb.created_at DESC";
                                                    break;
                                                default:
                                                    $orderBy = ' ORDER BY eb.created_at DESC';
                                                    break;
                                            }

                                            if($role != 'admin')
                                            {
                                                $where[] = "eb.branch_id = ?";
                                            }

                                            $where_sql = '';
                                            if (!empty($where)) 
                                            {
                                                $where_sql = " WHERE " . implode(" AND ", $where);;
                                            }

                                            /*Count*/
                                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                            $limit = 5;
                                            $offset = ($page - 1) * $limit;

                                            $base_sql = " FROM eb_transactions AS eb
                                                          INNER JOIN branches AS b ON eb.branch_id = b.branch_id";
                                                            
                                            $sql = "SELECT eb.eb_id, eb.branch_id, eb.label, eb.amount, eb.type_of_transac, eb.created_by, eb.created_at"
                                                    . $base_sql
                                                    . $where_sql
                                                    . $orderBy
                                                    . " LIMIT " . $limit . " OFFSET " . $offset;

                                            $count_sql = "SELECT COUNT(*) AS total
                                                          FROM eb_transactions AS eb
                                                          INNER JOIN branches AS b ON eb.branch_id = b.branch_id
                                                          $where_sql";

                                            $stmt = $conn->prepare($sql);
                                            $count_stmt = $conn->prepare($count_sql);

                                            if($role != 'admin')
                                            {
                                                $stmt->bind_param("i", $branch_id);
                                                $count_stmt->bind_param("i", $branch_id);
                                            }

                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $count_stmt->execute();
                                            $count_result = $count_stmt->get_result();
                                            $total_row = $count_result ? $count_result->fetch_assoc() : null;
                                            $total = $total_row['total'] ?? 0;
                                            
                                            $number = (($page - 1) * $limit) + 1;

                                            if($result->num_rows > 0) 
                                            {
                                                while($row = $result->fetch_assoc())
                                                {
                                                    $eb_id = htmlspecialchars($row['eb_id']);
                                                    $eb_b_id = htmlspecialchars($row['branch_id']);
                                                    $eb_label = htmlspecialchars($row['label']);
                                                    $eb_amount = htmlspecialchars($row['amount']);
                                                    $eb_type = htmlspecialchars($row['type_of_transac']);
                                                    $eb_creator = htmlspecialchars($row['created_by']);
                                                    $eb_c_date = htmlspecialchars($row['created_at']);

                                                    $format_date = date("M d, Y", strtotime($eb_c_date));

                                                    echo 
                                                    "
                                                    <tr>
                                                        <td>$number</td>
                                                        <td>$format_date</td>
                                                        <td>$eb_label</td>
                                                        <td>₱ $eb_amount</td>
                                                        <td>$eb_type</td> 
                                                        <td>
                                                            <form action=\"\" method=\"POST\" onsubmit=\"return confirm('Are you sure you want to delete this transaction?');\">
                                                                <input type=\"hidden\" name=\"delete_id\" value=\"$eb_id\">
                                                                <button type=\"submit\" name=\"confirm_delete\">
                                                                    <img src=\"../../resources/img/icons/delete2.png\" alt=\"delete\">
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                    ";

                                                    $number++;
                                                }
                                            }
                                            else 
                                            {
                                                echo
                                                "
                                                <tr>
                                                    <td colspan=\"4\" style=\"text-align: center; padding: 1rem 0;\">No End Balance Transactions Recorded Yet</td>
                                                </tr>
                                                ";
                                            }

                                            if (isset($_POST['confirm_delete'])) 
                                            {
                                                $delete_id = htmlspecialchars($_POST['delete_id']);
                                                $upd_success = false;

                                                $sql = "SELECT branch_id, amount, type_of_transac FROM eb_transactions WHERE eb_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("i", $delete_id);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $row = $result->fetch_assoc();

                                                $fetch_b_id = htmlspecialchars($row['branch_id']);
                                                $fetch_amount = (float)htmlspecialchars($row['amount']);
                                                $fetch_type = htmlspecialchars($row['type_of_transac']);

                                                $sql = "SELECT end_balance FROM branches where branch_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("i", $fetch_b_id);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $row = $result->fetch_assoc();

                                                $fetch_eb = (float)htmlspecialchars($row['end_balance']);

                                                $sql = "UPDATE branches
                                                        SET end_balance = ?
                                                        WHERE branch_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                if($fetch_type == "Debit")
                                                {
                                                    $new_eb_val = $fetch_eb + $fetch_amount;
                                                    $stmt->bind_param("di", $new_eb_val, $fetch_b_id);
                                                    if($stmt->execute())
                                                    {
                                                        $upd_success = true;
                                                    }
                                                }
                                                else if($fetch_type == "Credit")
                                                {
                                                    $new_eb_val = $fetch_eb - $fetch_amount;
                                                    $stmt->bind_param("di", $new_eb_val, $fetch_b_id);
                                                    if($stmt->execute())
                                                    {
                                                        $upd_success = true;
                                                    }
                                                }

                                                if($upd_success)
                                                {
                                                    $sql = "DELETE FROM eb_transactions WHERE eb_id = ?"; 
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $delete_id);
                                                    if ($stmt->execute()) 
                                                    {
                                                        $audit_u_id = $_SESSION['user_id'];
                                                        $audit_action = "Deleted";
                                                        $audit_obj = "EB Transaction";
                                                        $audit_desc = "Deleted end bal transaction with amount '$fetch_amount'";

                                                        $curDate = new DateTime();
                                                        $current = $curDate->format('Y-m-d H:i:s');

                                                        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                VALUES (?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $fetch_b_id, $current);
                                                        if($stmt->execute())
                                                        { 
                                                            $_SESSION['update_eb_success_msg'] = "Transaction deleted successfully!";
                                                            header("Location: " . $_SERVER['PHP_SELF']);
                                                            exit();
                                                        }
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
                                        <button onclick="window.location.href='balance_audit.php'"><img src="../../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                        <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <div class="result_cont_bar">
        <?php
            //$_SESSION['update_eb_success_msg'] = 'Test';

            if(isset($_SESSION['update_eb_success_msg']))
            {
                echo "<span id=\"update_success\" class=\"message_success_d\"><img src=\"../../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['update_eb_success_msg'] . "</span>";
    
                echo "
                    <script>
                        // Function to hide the element
                        function hideMessage() {
                            var element = document.getElementById('update_success');
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

                unset($_SESSION['update_eb_success_msg']);
            }
            else 
            {
                unset($_SESSION['update_eb_success_msg']);
            }
        ?>
    </div>
    <div id="overlay" class="overlay_hidden"></div>
    <div id="popup" class="popup_hidden">
        <div class="popup_content">
            <div class="popup_top">
                <h2>Update Starting Balance</h2>
                <button id="closePopup"><img src="../../resources/img/icons/close.png" alt="close"></button>
            </div>
            <hr>
            <div class="popup_main">
                <div class="card_form">
                    <?php 
                        $sql = "SELECT start_balance FROM branches WHERE branch_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $select_br);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if($result->num_rows > 0)
                        {
                            $row = $result->fetch_assoc();
                            $start_bal = htmlspecialchars($row['start_balance']);
                        }
                    ?>
                    <form action="" method="POST">
                        <div class="input_cont">
                            <input type="hidden" name="selected_br" value="<?php echo $select_br; ?>">
                            <label for="penalty">End Balance Amount<i style="color:red;">*</i></label>
                            <input type="number" name="new_sb" id="new_sb" step="any" value="<?php echo $start_bal; ?>" required></input>
                        </div>
                        <div class="modal-actions">
                            <button type="submit" id="update" name="update" class="btn-proceed"><img src="../../resources/img/icons/arrow_circle_right.png" alt="proceed">Update</button>
                        </div>
                    </form>
                </div>
                <?php 
                    if(isset($_POST['update']))
                    {
                        $target_br = (int)$_POST['selected_br'];
                        $new_sb = htmlspecialchars($_POST['new_sb']);

                        if (filter_var($new_sb, FILTER_VALIDATE_FLOAT) !== false)
                        {
                            $sql = "SELECT (start_balance - end_balance) AS bal_diff FROM branches WHERE branch_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $target_br);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $row = $result->fetch_assoc();
                            
                            $bal_diff = (float)htmlspecialchars($row['bal_diff']);
                            $new_sb = round((float)$new_sb, 2);

                            if($bal_diff == 0.00)
                            {
                                $sql = "UPDATE branches
                                        SET start_balance = ?, end_balance = ?
                                        WHERE branch_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ddi", $new_sb, $new_sb, $target_br);
                                if($stmt->execute())
                                {
                                    $_SESSION['update_eb_success_msg'] = "End Balance Successfully Updated!";
                                    header('Location: ' . $_SERVER['PHP_SELF']);
                                    exit();
                                }
                                else 
                                {
                                    //Error msg dito
                                }
                            }
                            else 
                            {
                                $new_eb_diff = round((float)$new_sb, 2) - $bal_diff;
                                $sql = "UPDATE branches
                                        SET start_balance = ?, end_balance = ?
                                        WHERE branch_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ddi", $new_sb, $new_eb_diff, $target_br);
                                if($stmt->execute())
                                {
                                    $_SESSION['update_eb_success_msg'] = "End Balance Successfully Updated!";
                                    header('Location: ' . $_SERVER['PHP_SELF']);
                                    exit();
                                }
                                else 
                                {
                                    //Error msg dito
                                }
                            }
                        }
                        else
                        {
                            //Error msg ule
                        }
                    }
                ?>
            </div>        
        </div>
    </div>
    <script>
        /*dat iload muna lahat ng page ung needed elements bago maclick eto*/
        document.addEventListener("DOMContentLoaded", function() {
            /*hanapin nya muna si button sa .archive_btn_cont*/
            /*hanapin din si overlay tas popup then yung close button*/
            const openBtn = document.querySelector(".form_conts #edit_btn");
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
</body>
</html>