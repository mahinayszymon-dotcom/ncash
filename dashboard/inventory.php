<?php
ob_start();
include("../config/session_check.php");
include("../config/db_conn.php");
include("../db/branch_fetch.php");

date_default_timezone_set('Asia/Manila');
$is_readonly = $_SESSION['is_readonly'];

$curDate = new DateTime();
$current = $curDate->format('Y-m-d');

$sql = "UPDATE inventory
        SET status = 'Overdue'
        WHERE DATE(due_date) < ?
        AND status = 'Active'";
    
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current);
$stmt->execute();


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/inventory.css">
</head>
<body>
    <main class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <?php
                include('../includes/top_panel.php')
            ?>
            <div class="central_panelB">
                <div class="hero_panel">
                    <div class="hero_content">
                        <h2>Live Inventory Management</h2>
                        <p>This page is your primary tool for overseeing non-redeemed pawned items. To view paid and historical records, use the 'Visit Archive' button. New entries are created via the 'Add Item' button on the main table.</p>
                    </div>
                    <div class="hero_action">
                        <button onclick="window.location.href='../archives/archived_items.php'">Visit Archive</button>
                    </div>
                </div>
                <div class="data_tableB">
                    <div class="data_panel_header">
                        <div class="data_panel_name">
                            <div class="icon_normal">
                                <img src="../resources/img/icons/table_w.png" alt="table_icon">
                            </div>
                            <h2>Data Tabulation</h2>
                        </div>
                        <div class="data_panel_buttons">
                            <div class="search_cont">
                                <input type="text" placeholder="Search">
                                <img src="../resources/img/icons/search.png" alt="search">
                            </div>
                            <?php
                                try 
                                {
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
                                } 
                                catch (Exception $e)
                                {
                                    $_SESSION['error_msg'] = 'Backend Error: ' . $e->getMessage();
                                }  
                            ?>
                            <form action="inventory.php" method="GET">
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
                                            <option value='default' " . ($selected === 'default' ? 'selected' : '') . ">Default Sorting</option>
                                            ";
                                        }
                                    ?>
                                    <option value="nameAZ" <?= $selected === 'nameAZ' ? 'selected' : '' ?>>Name (A-Z)</option>
                                    <option value="nameZA" <?= $selected === 'nameZA' ? 'selected' : '' ?>>Name (Z-A)</option>
                                    <option value="price_increasing" <?= $selected === 'price_increasing' ? 'selected' : '' ?>>Price (Increasing)</option>
                                    <option value="price_decreasing" <?= $selected === 'price_decreasing' ? 'selected' : '' ?>>Price (Decreasing)</option>
                                    <option value="date_increasing" <?= $selected === 'date_increasing' ? 'selected' : '' ?>>Due Date (Old)</option>
                                    <option value="date_decreasing" <?= $selected === 'date_decreasing' ? 'selected' : '' ?>>Due Date (New)</option>
                                    <option value="active" <?= $selected === 'active' ? 'selected' : '' ?>>Active Items</option>
                                    <option value="overdue" <?= $selected === 'overdue' ? 'selected' : '' ?>>Overdue Items</option>
                                    <option value="redeemed" <?= $selected === 'redeemed' ? 'selected' : '' ?>>Redeemed Items</option>
                                </select>
                                <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                            </form>
                            <?php 
                                if($is_readonly == 0)
                                {
                                    echo "<button onclick=\"window.location.href='../dashboard/inventory/add.php'\"><img src=\"../resources/img/icons/add1.png\" alt=\"add_item\">Add Item</button>";
                                }
                            ?>
                            <?php
                                /*Sorting*/
                                $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                $where = [];
                                $orderBy = '';

                                switch ($sorting)
                                {
                                    case 'all':
                                    case 'default':
                                        $orderBy = " ORDER BY i.agreement_num DESC";
                                        break;
                                    case 'pasig':
                                        $where[] = "b.branch_name = 'Marikina-Pasig'";
                                        $orderBy = " ORDER BY i.agreement_num DESC";
                                        break;
                                    case 'quezon':
                                        $where[] = "b.branch_name = 'Quezon City'";
                                        $orderBy = " ORDER BY i.agreement_num DESC";
                                        break;
                                    case 'makati': 
                                        $where[] = "b.branch_name = 'Makati'";
                                        $orderBy = " ORDER BY i.agreement_num DESC";
                                        break;
                                    case 'nameAZ': 
                                        $orderBy = " ORDER BY c.fullname ASC";
                                        break;
                                    case 'nameZA': 
                                        $orderBy = " ORDER BY c.fullname DESC";
                                        break;
                                    case 'price_increasing': 
                                        $orderBy = " ORDER BY i.principal ASC";
                                        break;
                                    case 'price_decreasing': 
                                        $orderBy = " ORDER BY i.principal DESC";
                                        break;
                                    case 'date_increasing':
                                        $orderBy = " ORDER BY i.due_date ASC";
                                        break;
                                    case 'date_decreasing':
                                        $orderBy = " ORDER BY i.due_date DESC";
                                        break;
                                    case 'active':
                                        $where[] = "i.status = 'Active'";
                                        break;
                                    case 'overdue':
                                        $where[] = "i.status = 'Overdue'";
                                        break;
                                    case 'redeemed':
                                        $where[] = "i.status = 'Redeemed'";
                                        break;
                                    default:
                                        $orderBy = '';
                                        break;
                                }

                                if($role != 'admin')
                                {
                                    $where[] = "i.branch_id = ?"; //If not admin, only display items from that branch
                                }

                                $where_sql = '';
                                if (!empty($where)) 
                                {
                                    // $sql .= " WHERE " . implode(" AND ", $where);
                                    $where_sql = " WHERE " . implode(" AND ", $where);
                                }

                                /*Count*/
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $limit = 8;
                                $offset = ($page - 1) * $limit;

                                $sql = "SELECT i.item_id, i.agreement_num, c.fullname, i.item_name, i.principal, b.branch_name, i.status, i.due_date, i.created_at
                                    FROM inventory AS i
                                    INNER JOIN clients AS c ON i.client_id = c.client_id
                                    INNER JOIN branches AS b ON i.branch_id = b.branch_id
                                    $where_sql
                                    $orderBy
                                    LIMIT $limit OFFSET $offset";
                                
                                $count_sql = "SELECT COUNT(*) AS total
                                    FROM inventory AS i
                                    INNER JOIN clients AS c ON i.client_id = c.client_id
                                    INNER JOIN branches AS b ON i.branch_id = b.branch_id
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
                                    while($inv_row = $result->fetch_assoc())
                                    {
                                        $item_id = htmlspecialchars($inv_row['item_id']);
                                        $agreement_num = htmlspecialchars($inv_row['agreement_num']);
                                        $client_name = ucwords(htmlspecialchars($inv_row['fullname']));
                                        $item = htmlspecialchars($inv_row['item_name']);
                                        $principal = htmlspecialchars($inv_row['principal']);
                                        $due_date = htmlspecialchars($inv_row['due_date']);
                                        $branch = htmlspecialchars($inv_row['branch_name']);
                                        $status = htmlspecialchars($inv_row['status']);

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
        
                                        $format_date = date("M d, Y", strtotime($due_date));
                                        
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
                                                        <a href=\"../dashboard/inventory/item_details.php?id=" . $item_id . "\"><button><img src=\"../resources/img/icons/open.png\" alt=\"open\"></button></a>
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
                                include("../includes/pagination.php");
                            ?>
                        </div>
                        <div class="data_table_actions_components">
                            <div class="data_actions">
                                
                                <!--Supposedly dapat naka disable eto. kapag nag check ako sa ilang checkbox, tsaka lang sya mag enable. Dito na ren si multiple selection-->
                                <!-- <button><img src="../resources/img/icons/edit.png" alt="edit"><p>Edit</p></button>
                                <button><img src="../resources/img/icons/archive.png" alt="archive"><p>Archive</p></button> -->
                                <button onclick="window.location.href='../dashboard/inventory.php'"><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                            
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <div class="result_cont_bar">
        <?php
            //$_SESSION['archive_success_msg'] = 'Test';

            if (isset($_SESSION['archive_success_msg'])) {
                echo "<span id=\"archive_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['archive_success_msg'] . "</span>";
    
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('archive_success');
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

                unset($_SESSION['archive_success_msg']);
            } else if (isset($_SESSION['success_msg'])) {
                echo "<span id=\"success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['success_msg'] . "</span>";
    
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('success');
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

                unset($_SESSION['success_msg']);
            } else if (isset($_SESSION['change_success_msg'])) {
                echo "<span id=\"change_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
    
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('change_success');
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

                unset($_SESSION['change_success_msg']);
            } else {
                unset($_SESSION['archive_success_msg']);
                unset($_SESSION['success_msg']);
                unset($_SESSION['change_success_msg']);
            }
        ?>
    </div>
</body>
</html>