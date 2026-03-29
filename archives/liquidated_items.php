<?php
include("../config/session_check.php");  // pang check ng session
include("../config/db_conn.php");   // pang connect sa db
include("../db/branch_fetch.php"); // para kunin ung related sa branch

$_SESSION['previous_link'] = $_SERVER['PHP_SELF'];

$is_readonly = $_SESSION['is_readonly'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidated Items</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/inventory.css">
    <link rel="stylesheet" href="../resources/css/pages/archives/archive.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        .nav_links ul li:nth-child(3) {
            background-color: transparent;
            opacity: 0.8;
        }

        .nav_links ul li:nth-child(3) img {
            opacity: 0.8;
        }

        .archive_nav_links a:nth-child(4) {
            background-color: var(--blue) !important;
            color: var(--main-content) !important;
            opacity: 1;
        }

        .archive_nav_links a:nth-child(4):hover {
            border: none;
            border-radius: 5px;
            /* background-color: var(--red-dark) !important; */
            color: var(--main-content) !important;
            opacity: 1;
        }
    </style>
</head>
<body>
    <main>
        <section class="dashboard">
            <section class="navigation_bar">
                <?php
                    include('../includes/nav_bar.php')
                ?>
            </section>
            <section class="main_content">
                <?php
                    include('../includes/top_panel.php')
                ?>
                <div class="central_panelC">
                    <div class="data_tableC">
                        <div class="data_panel_subheaderC">
                            <div class="archive_nav_links">
                                <a href="archived_transactions.php">Transactions</a>
                                <a href="archived_items.php">Items</a>
                                <a href="archived_clients.php">Clients</a>
                                <a href="liquidated_items.php">Liquidated</a>
                            </div>
                        </div>
                        <br>
                        <div class="data_panel_header">
                            <div class="data_panel_name">
                                <div class="icon_normal">
                                    <img src="../resources/img/icons/archive_w.png" alt="table_icon">
                                </div>
                                <h2>Archived Data Tabulation</h2>
                            </div>
                            <div class="data_panel_buttonsC">
                                <div class="search_cont">
                                    <input type="text" placeholder="Search">
                                    <img src="../resources/img/icons/search.png" alt="search">
                                </div>
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
                                <form action="archived_transactions.php" method="GET">
                                    <span class="custom-arrow-sort"><img src="../resources/img/icons/filter.png" alt="filter"></span>
                                    <select name="branch" id="branch" onchange="this.form.submit()" class="sort">
                                        <?php
                                            $selected = $_GET['branch'] ?? 'all'; 
        
                                            if ($role == 'admin') 
                                            {
                                                echo "
                                                <option value='all' " . ($selected === 'all' ? 'selected' : '') . ">All Branch</option>
                                                <option value='pasig' " . ($selected === 'pasig' ? 'selected' : '') . ">Pasig City Branch</option>
                                                <option value='quezon' " . ($selected === 'quezon' ? 'selected' : '') . ">Quezon City Branch</option>
                                                <option value='makati' " . ($selected === 'makati' ? 'selected' : '') . ">Makati City Branch</option>
                                                ";
                                            }
                                            else if ($role == 'user')
                                            {
                                                echo "
                                                <option value='all' " . ($selected === 'default' ? 'selected' : '') . ">Default Sorting</option>
                                                ";
                                            }
                                        ?>
                                        <option value="nameAZ" <?= $selected === 'nameAZ' ? 'selected' : '' ?>>Name (A-Z)</option>
                                        <option value="nameZA" <?= $selected === 'nameZA' ? 'selected' : '' ?>>Name (Z-A)</option>
                                        <option value="price_increasing" <?= $selected === 'price_increasing' ? 'selected' : '' ?>>Price (Increasing)</option>
                                        <option value="price_decreasing" <?= $selected === 'price_decreasing' ? 'selected' : '' ?>>Price (Decreasing)</option>
                                    </select>
                                    <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                                </form>
                                <?php
                                    /*Sorting*/
                                    $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                    $where = [];
                                    $orderBy = '';

                                    switch ($sorting)
                                    {
                                        case 'all':
                                        case 'default':
                                            $orderBy = " ORDER BY il.updated_at DESC";
                                            break;
                                        case 'pasig':
                                            $where[] = "b.branch_name = 'Marikina-Pasig'";
                                            $orderBy = " ORDER BY il.updated_at DESC";
                                            break;
                                        case 'quezon':
                                            $where[] = "b.branch_name = 'Quezon City'";
                                            $orderBy = " ORDER BY il.updated_at DESC";
                                            break;
                                        case 'makati': 
                                            $where[] = "b.branch_name = 'Makati'";
                                            $orderBy = " ORDER BY il.updated_at DESC";
                                            break;
                                        case 'nameAZ': 
                                            $orderBy = " ORDER BY COALESCE(c.fullname, ca.fullname) ASC";
                                            break;
                                        case 'nameZA': 
                                            $orderBy = " ORDER BY COALESCE(c.fullname, ca.fullname) DESC";
                                            break;
                                        case 'price_increasing': 
                                            $orderBy = " ORDER BY il.amount ASC";
                                            break;
                                        case 'price_decreasing': 
                                            $orderBy = " ORDER BY il.amount DESC";
                                            break;
                                        default:
                                            $orderBy = '';
                                            break;
                                    }

                                    if($role != 'admin')
                                    {
                                        $where[] = "il.branch_id = ?"; //If not admin, only display items from that branch
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

                                    $sql = "SELECT il.liquidation_id, il.liquidated_at, il.agreement_num, COALESCE(c.fullname, ca.fullname) AS fullname, il.item_id, il.client_id, il.item_name, il.principal, b.branch_name, il.status, il.due_date, il.created_at
                                        FROM items_liquidated AS il
                                        LEFT JOIN clients AS c ON il.client_id = c.client_id
                                        LEFT JOIN clients_archive AS ca ON il.client_id = ca.client_id
                                        INNER JOIN branches AS b ON il.branch_id = b.branch_id
                                        $where_sql
                                        $orderBy
                                        LIMIT $limit OFFSET $offset";

                                    $count_sql = "SELECT COUNT(*) AS total
                                        FROM items_liquidated AS il
                                        LEFT JOIN clients AS c ON il.client_id = c.client_id
                                        LEFT JOIN clients_archive AS ca ON il.client_id = ca.client_id
                                        INNER JOIN branches AS b ON il.branch_id = b.branch_id
                                        $where_sql";

                                    // $sql .= " $orderBy LIMIT $limit OFFSET $offset";

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
                                ?>
                            </div>
                        </div>
                        <div class="table_cont">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>AN Code</th>
                                        <th>Item Name</th>
                                        <th>Client Name</th>
                                        <th>Due Date</th>
                                        <th>Principal</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $number = (($page - 1) * $limit) + 1;

                                    if($result->num_rows > 0) 
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            $item_id = htmlspecialchars($row['item_id']);
                                            $agreement_num = htmlspecialchars($row['agreement_num']);
                                            $client_name = htmlspecialchars($row['fullname']);
                                            $client_id = htmlspecialchars($row['client_id']);
                                            $item = htmlspecialchars($row['item_name']);
                                            $principal = htmlspecialchars($row['principal']);
                                            $due_date = htmlspecialchars($row['due_date']);
                                            $branch = htmlspecialchars($row['branch_name']);
                                            $status = htmlspecialchars($row['status']);

                                            $principal_decimal = number_format($principal, 2);
                                            
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
            
                                            $format_date = date("d M Y", strtotime($due_date));
                                            
                                            if ($branch === "Marikina-Pasig") {
                                                $final_agreement_num = "MP" . $agreement_num;
                                            } else if ($branch === "Quezon City") {
                                                $final_agreement_num = "Q" . $agreement_num;
                                            } else if ($branch === "Makati") {
                                                $final_agreement_num = "M" . $agreement_num;
                                            } else {
                                                $final_agreement_num = "-" . $agreement_num;
                                            }

                                                if (isset($_SESSION['error_msg'])) {
                                                    echo '<div class="result_cont">';
                                                    echo "<span class=\"message_error\"><img src=\"../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['error_msg'] . "</span>";
                                                    unset($_SESSION['error_msg']);
                                                    echo '</div>';
                                                } else {
                                                    echo 
                                                    "
                                                    <tr>
                                                        <td>$number</td>
                                                        <td>$final_agreement_num</td>
                                                        <td>$item</td>
                                                        <td>$client_name</td>
                                                        <td>$format_date</td>
                                                        <td>₱ $principal_decimal</td>
                                                        <td><span style=\"$status_style\">$status</span></td>  
                                                        <td>
                                                            <a href=\"../archives/liquidated_details.php?id=" . $item_id . "\"><button><img src=\"../resources/img/icons/open.png\" alt=\"open\"></button></a>
                                                        </td>
                                                    </tr>
                                                    ";
                                                }

                                            $number++;
                                        }
                                    }
                                    else
                                    {
                                        echo
                                        "
                                            <tr style='height: 43vh; border: none; cursor: auto;'>
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
                                <?php
                                    include("../includes/pagination.php")
                                ?>
                            </div>
                            <div class="data_table_actions_components">
                                <div class="data_actions">
                                    <button><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                    <span style="font-size: 1rem; color: var(--success); background-color: #e1ede2; padding: 0.6rem 0.5rem; border-radius: 0.5rem; font-weight: 400;">
                                        Total Amount: ₱ 
                                        <?php
                                            $sql = "SELECT SUM(principal) AS total_principal FROM items_liquidated";
                                            $sum_stmt = $conn->prepare($sql);
                                            $sum_stmt->execute();
                                            $sum_result = $sum_stmt->get_result();
                                            $sum_row = $sum_result->fetch_assoc();
                                            $total_liquidated = htmlspecialchars($sum_row['total_principal']);

                                            echo $total_liquidated;
                                        ?>
                                    </span>
                                    <?php  
                                        $lq_role = $role;
                                        if($lq_role === "admin")
                                        {
                                            $lq_branch = $selected;
                                        }
                                        else 
                                        {
                                            $lq_b_id = $_SESSION['branch_id'];
                                            switch ($lq_b_id)
                                            {
                                                case 1100:
                                                    $lq_branch = "pasig";
                                                    break;
                                                case 1101:
                                                    $lq_branch = "quezon";
                                                    break;
                                                case 1102:
                                                    $lq_branch = "makati";
                                                    break;
                                            }
                                        }

                                        if($is_readonly == 0)
                                        {
                                            echo '<button id="turnover" onclick="prepLiquid()" 
                                                   data-role="' . $lq_role . '" 
                                                   data-branch="' . $lq_branch . '" 
                                                   style="background-color: var(--purple); color: var(--main-content);"><img src="../resources/img/icons/archive_w.png" alt="turnover"><p style="color: var(--main-content);">Turnover</p></button>';
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
    <script src="../resources/js/report_download.js"></script>
    <div class="result_cont_bar">
        <?php
            //$_SESSION['archive_success_msg'] = 'Test';

            if (isset($_SESSION['renew_success_msg'])) {
                echo "<span id=\"renew_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['renew_success_msg'] . "</span>";
    
                // 2. Add JavaScript immediately after the message to hide it after 3 seconds
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('renew_success');
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

                unset($_SESSION['renew_success_msg']);
            }
        ?>
    </div>
</body>
<div id="reportModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalBranchName">Branch Report</h2>
            <button class="close_button" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalDataContainer">
                <p>Loading report data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="window.print()" class="print_button"><img src="../resources/img/icons/print.png" alt="print">Print Report</button>
        </div>
    </div>
</div>
</html>