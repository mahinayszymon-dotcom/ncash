<?php
include("../config/session_check.php");  // pang check ng session
include("../config/db_conn.php");   // pang connect sa db
include("../db/branch_fetch.php"); // para kunin ung related sa branch
include("../db/db_opening_updates.php"); // updates db for several operations
require_once '../db/month_end_snapshot.php'; // sa kpi

include("../includes/send_reminders.php");

$is_readonly = $_SESSION['is_readonly'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TraceMo Dashboard</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/home.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
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
            <div class="central_panelE">
                <div class="quick_cont">
                    <?php
                        if($is_readonly == 0)
                        {
                            echo '<a href="../dashboard/inventory/add.php"><span class="img_glass"><img src="../resources/img/icons/add_box2.png" alt="add"></span>Add Item</a>
                                  <a href="../dashboard/transactions/add.php"><span class="img_glass"><img src="../resources/img/icons/payment.png" alt="pay"></span>Add Transaction</a>';
                        }

                        if($role !== 'admin') {
                            echo '<a href="reports.php"><span class="img_glass"><img src="../resources/img/icons/reports2.png" alt="report"></span>View Reports</a>';
                        }
                    ?>
                    <a href="../archives/archived_items.php"><span class="img_glass"><img src="../resources/img/icons/archives.png" alt="archive"></span>View Archives</a>
                    <a href="../archives/liquidated_items.php"><span class="img_glass"><img src="../resources/img/icons/list.png" alt="hold"></span>Liquidation List</a>
                    <!-- <a href="userhelp.php"><span class="img_glass"><img src="../resources/img/icons/help2.png" alt="help"></span>Help Center</a> -->
                </div>
                <div class="main_home">
                    <div class="top_section">
                        <?php
                            include("../includes/critical_analytics_tabs.php")
                        ?>
                    </div>
                    <div class="data_tableB">
                        <div class="data_panel_header">
                            <div class="data_panel_name">
                                <div class="icon_normal">
                                    <img src="../resources/img/icons/recent_tab_w.png" alt="recent_icon">
                                </div>
                                <h2>Upcoming Priority Renewals</h2>
                            </div>
                            <div class="data_panel_buttons">
                                <?php 
                                    include '../includes/search_bar.php'; 
                                
                                    $searchColumns = [
                                        'i.item_id',
                                        'i.agreement_num',
                                        'c.fullname',
                                        'i.due_date',
                                        'i.principal',
                                        'i.item_name',
                                        'i.status',
                                        'i.item_name',
                                        'b.branch_name',
                                        "DATE_FORMAT(i.due_date, '%b %d, %Y')",
                                        "DATE_FORMAT(i.due_date, '%M %d, %Y')" 
                                    ];

                                    $where = [];

                                    include '../includes/search_handler.php';
                                
                                    $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                    $orderBy = '';
                                    $where_sql = '';

                                    switch ($sorting)
                                    {
                                        case 'all':
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
                                        case 'default':
                                            $orderBy = " ORDER BY i.due_date ASC";
                                            break;
                                        case 'active':
                                            $where[] = "i.status = 'Active'";
                                            break;
                                        case 'overdue':
                                            $where[] = "i.status = 'Overdue'";
                                            break;
                                        default:
                                            $orderBy = '';
                                            break;
                                    }

                                    if($role != 'admin')
                                    {
                                        $where[] = "i.branch_id = ?";
                                    }

                                    $where[] = "i.status = 'Active'"; 
                                    $where[] = "i.due_date >= CURDATE()";
                                    $where[] = "i.due_date < DATE_ADD(CURDATE(), INTERVAL 8 DAY)"; //This

                                    if (!empty($where)) 
                                    {
                                        $where_sql = " WHERE " . implode(" AND ", $where);;
                                    }

                                    /*Count*/
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $limit = 5;
                                    $offset = ($page - 1) * $limit;

                                    $base_sql = " FROM inventory AS i
                                                    INNER JOIN clients AS c ON i.client_id = c.client_id
                                                    INNER JOIN branches AS b ON i.branch_id = b.branch_id";
                                                    
                                    $sql = "SELECT i.item_id, i.agreement_num, i.due_date, c.fullname, i.item_name, i.principal, b.branch_name, i.status, i.created_at "
                                            . $base_sql
                                            . $where_sql
                                            . $orderBy
                                            . " LIMIT " . $limit . " OFFSET " . $offset;
                                        
                                    $count_sql = "SELECT COUNT(*) AS total "
                                            . $base_sql
                                            . $where_sql;
                                    
                                    $stmt = $conn->prepare($sql);
                                    $count_stmt = $conn->prepare($count_sql);   
       
                                    // $sql .= $orderBy;
                                    // $sql .= " LIMIT 7"; // Ni-limit ko muna sa 7

                                    $params = [];
                                    $types = "";

                                    // branch filter
                                    if ($role != 'admin') {
                                        $types .= "i";
                                        $params[] = $branch_id;
                                    }

                                    // search values
                                    if (!empty($searchValues)) {
                                        $types .= str_repeat("s", count($searchValues));
                                        $params = array_merge($params, $searchValues);
                                    }

                                    // bind if there are params
                                    if (!empty($params)) {
                                        $stmt->bind_param($types, ...$params);
                                        $count_stmt->bind_param($types, ...$params);
                                    }

                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    $total_row = $count_result ? $count_result->fetch_assoc() : null;
                                    $total = $total_row['total'] ?? 0;
                                ?>
                                <form action="home.php" method="GET">
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
                                        <option value="date_increasing" <?= $selected === 'date_increasing' ? 'selected' : '' ?>>Due Date (Old)</option>
                                        <option value="date_decreasing" <?= $selected === 'date_decreasing' ? 'selected' : '' ?>>Due Date (New)</option>
                                    </select>
                                    <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                                </form>
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
                                        $number = 1;

                                        if($result->num_rows > 0) 
                                        {
                                            while($row = $result->fetch_assoc())
                                            {
                                                $item_id = htmlspecialchars($row['item_id']);
                                                $agreement_num = htmlspecialchars($row['agreement_num']);
                                                $client_name = htmlspecialchars($row['fullname']);
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

                                                $number++;
                                            }
                                        }
                                        else
                                        {
                                            echo
                                            "
                                                <tr style='height: auto; border: none; cursor: auto;'>
                                                    <td rowspan='1' colspan='8' class='no_records_found'> 
                                                        <br>
                                                        <h3 style='font-size: 18px;'>No Records Found</h3>          
                                                        <p style='font-size: 15px; opacity: 0.85;'>Try adding new data or search a different category.</p>
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
                                    
                                    <!--Supposedly dapat naka disable eto. kapag nag check ako sa ilang checkbox, tsaka lang sya mag enable. Dito na ren si multiple selection-->
                                    <!-- <button><img src="../resources/img/icons/edit.png" alt="edit"><p>Edit</p></button>
                                    <button><img src="../resources/img/icons/archive.png" alt="archive"><p>Archive</p></button> -->
                                    <button onclick="window.location.href='home.php'"><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                    <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>