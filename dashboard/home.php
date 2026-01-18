<?php
include("../config/session_check.php");  // pang check ng session
include("../config/db_conn.php");   // pang connect sa db
include("../db/branch_fetch.php"); // para kunin ung related sa branch

date_default_timezone_set('Asia/Manila');
$curDate = new DateTime();
$current = $curDate->format('Y-m-d');

$sql = "UPDATE inventory
        SET status = 'Overdue'
        WHERE DATE(due_date) < ?
        AND status = 'Active'";
    
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current);
$stmt->execute();
//pwede ilagay dto ung sa pagdelete ng mga may deletion period na clients
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
                    <a href="../dashboard/inventory/add.php"><span class="img_glass"><img src="../resources/img/icons/add_box2.png" alt="add"></span>Add Item</a>
                    <a href="../dashboard/transactions/add.php"><span class="img_glass"><img src="../resources/img/icons/payment.png" alt="pay"></span>Add Transaction</a>
                    <?php
                        if($role !== 'admin') {
                            echo '<a href="reports.php"><span class="img_glass"><img src="../resources/img/icons/reports2.png" alt="report"></span>View Reports</a>';
                        }
                    ?>
                    <a href="../archives/archived_items.php"><span class="img_glass"><img src="../resources/img/icons/archives.png" alt="archive"></span>View Archives</a>
                    <a href="userhelp.php"><span class="img_glass"><img src="../resources/img/icons/help2.png" alt="help"></span>Help Center</a>
                </div>
                <div class="main_home">
                    <div class="top_section">
                        <div class="critical_analytics_tabs">
                            <div class="cat_top">
                                <div class="cat_top_text">
                                    <p>Total Active Tickets</p>
                                </div>
                                <div class="icon_normal">
                                    <img src="../resources/img/icons/pawn_w.png" alt="active_icon">
                                </div>
                            </div>
                            <div class="cat_mid">
                                <?php
                                    $role = $_SESSION['role'];

                                    $sql = "SELECT COUNT(*) AS active_count FROM inventory WHERE status = 'Active'";

                                    if($role != 'admin')
                                    {
                                        $sql .= " AND branch_id = ?";
                                    }

                                    $stmt = $conn->prepare($sql);

                                    if($role != 'admin')
                                    {
                                        $stmt->bind_param("i", $branch_id);
                                    }

                                    $stmt->execute();    
                                    $result = $stmt->get_result();

                                    $row = $result->fetch_assoc();
                                    $active_count = $row['active_count'];

                                    // trend 
                                    $trend_output_active = "0 %"; // test
                                    $trend_class = ""; 
                                    $percent_change_active = -5; // test

                                    if ($percent_change_active > 0) {
                                        $trend_output_active = "↑ " . round($percent_change_active, 2) . " %";
                                        $trend_class = "trend_up"; 
                                    } elseif ($percent_change_active < 0) {
                                        $trend_output_active = "↓ " . round(abs($percent_change_active), 2) . " %";
                                        $trend_class = "trend_down"; 
                                    } else {
                                        $trend_output_active = "No data";
                                        $trend_class = "trend_unknown"; 
                                    }
                                    // output
                                    echo "<p> $active_count </p>";
                                    echo "<p class=\"$trend_class\"> $trend_output_active </p>";
                                ?>
                            </div>
                            <div class="cat_bot">
                                <?php
                                    if($role != 'admin')
                                    {
                                        echo "<p>In $user_branch Branch</p>";
                                    }
                                    else if($role == 'admin')
                                    {
                                        echo "<p>All Branches</p>";
                                    }
                                ?>
                            </div>
                        </div>   
                        <div class="critical_analytics_tabs">
                            <div class="cat_top">
                                <div class="cat_top_text">
                                    <p>Total Redeemed Items</p>
                                </div>
                                <div class="icon_normal">
                                    <img src="../resources/img/icons/redeem_w.png" alt="redeemed_icon">
                                </div>
                            </div>
                            <div class="cat_mid">
                                <?php
                                    $sql = "SELECT COUNT(*) AS redeem_count FROM inventory WHERE status = 'Redeemed'";

                                    if($role != 'admin')
                                    {
                                        $sql .= " AND branch_id = ?";
                                    }

                                    $sql .= " AND YEARWEEK(updated_at, 1) = YEARWEEK(CURDATE(), 1)";
                                    $stmt = $conn->prepare($sql);

                                    if($role != 'admin')
                                    {
                                        $stmt->bind_param("i", $branch_id);
                                    }

                                    $stmt->execute();    
                                    $result = $stmt->get_result();

                                    $row = $result->fetch_assoc();
                                    $redeem_count = $row['redeem_count'];

                                    // trend 
                                    $trend_output_redeem = "0 %";
                                    $trend_class = ""; 
                                    $percent_change_redeem = 5; // test

                                    if ($percent_change_redeem > 0) {
                                        $trend_output_redeem = "↑ " . round($percent_change_redeem, 2) . " %";
                                        $trend_class = "trend_up"; 
                                    } elseif ($percent_change_redeem < 0) {
                                        $trend_output_redeem = "↓ " . round(abs($percent_change_redeem), 2) . " %";
                                        $trend_class = "trend_down"; 
                                    } else {
                                        $trend_output_redeem = "No data";
                                        $trend_class = "trend_unknown"; 
                                    }
                                    // output
                                    echo "<p> $redeem_count </p>";
                                    echo "<p class=\"$trend_class\"> $trend_output_redeem </p>";                                ?>
                            </div>
                            <div class="cat_bot">
                                <?php
                                    if($role != 'admin')
                                    {
                                        echo "<p>This Week ($user_branch)</p>";
                                    }
                                    else if($role == 'admin')
                                    {
                                        echo "<p>This Week (All Branches)</p>";
                                    }
                                ?>
                            </div>
                        </div>   
                        <div class="critical_analytics_tabs">
                            <div class="cat_top">
                                <div class="cat_top_text">
                                    <p>Total Overdue Items</p>
                                </div>
                                <div class="icon_overdue">
                                    <img src="../resources/img/icons/overdue_w.png" alt="overdue_icon">
                                </div>
                            </div>
                            <div class="cat_mid">
                                <?php
                                    $sql = "SELECT COUNT(*) AS over_count FROM inventory WHERE status = 'Overdue'";

                                    if($role != 'admin')
                                    {
                                        $sql .= " AND branch_id = ?";
                                    }

                                    $stmt = $conn->prepare($sql);

                                    if($role != 'admin')
                                    {
                                        $stmt->bind_param("i", $branch_id);
                                    }

                                    $stmt->execute();    
                                    $result = $stmt->get_result();

                                    $row = $result->fetch_assoc();
                                    $over_count = $row['over_count'];
                                    
                                    // trend 
                                    $trend_output_overdue = "0 %";
                                    $trend_class = ""; 
                                    $percent_change_over = 8; // test

                                    if ($percent_change_over > 0) {
                                        $trend_output_overdue = "↑ " . round($percent_change_over, 2) . " %";
                                        $trend_class = "trend_down"; 
                                    } elseif ($percent_change_over < 0) {
                                        $trend_output_overdue = "↓ " . round(abs($percent_change_over), 2) . " %";
                                        $trend_class = "trend_up"; 
                                    } else {
                                        $trend_output_overdue = "No data";
                                        $trend_class = "trend_unknown"; 
                                    }
                                    // output
                                    echo "<p> $over_count </p>";
                                    echo "<p class=\"$trend_class\"> $trend_output_overdue </p>";
                                ?>
                            </div>
                            <div class="cat_bot">
                                <?php
                                    if($role != 'admin')
                                    {
                                        echo "<p>In $user_branch Branch</p>";
                                    }
                                    else if($role == 'admin')
                                    {
                                        echo "<p>All Branches</p>";
                                    }
                                ?>
                            </div>
                        </div>   
                        <div class="critical_analytics_tabs">
                            <div class="cat_top">
                                <div class="cat_top_text">
                                    <p>Total Principal Outstanding</p>
                                </div>
                                <div class="icon_principal">
                                    <img src="../resources/img/icons/business_balance.png" alt="principal">
                                </div>
                            </div>
                            <div class="cat_mid">
                                <?php
                                    $role = $_SESSION['role'];

                                    $sql = "SELECT SUM(principal) AS total_principal FROM inventory WHERE status != 'Redeemed'";

                                    if($role != 'admin')
                                    {
                                        $sql .= " AND branch_id = ?";
                                    }

                                    $stmt = $conn->prepare($sql);

                                    if($role != 'admin')
                                    {
                                        $stmt->bind_param("i", $branch_id);
                                    }

                                    $stmt->execute();    
                                    $result = $stmt->get_result();

                                    $row = $result->fetch_assoc();
                                    $total_principal = $row['total_principal'];
                                    $formatted_principal = number_format($total_principal, 2);

                                    // trend 
                                    $trend_output_principal = "0 %";
                                    $trend_class = ""; 
                                    $percent_change_prin = 8; // test

                                    if ($percent_change_prin > 0) {
                                        $trend_output_principal = "↑ " . round($percent_change_prin, 2) . " %";
                                        $trend_class = "trend_up"; 
                                    } elseif ($percent_change_prin < 0) {
                                        $trend_output_principal = "↓ " . round(abs($percent_change_prin), 2) . " %";
                                        $trend_class = "trend_down"; 
                                    } else {
                                        $trend_output_principal = "No data";
                                        $trend_class = "trend_unknown"; 
                                    }
                                    // output
                                    echo "<p>₱ $formatted_principal </p>";
                                    echo "<p class=\"$trend_class\"> $trend_output_principal </p>";
                                ?>
                            </div>
                            <div class="cat_bot">
                                <?php
                                    if($role != 'admin')
                                    {
                                        echo "<p>In $user_branch Branch</p>";
                                    }
                                    else if($role == 'admin')
                                    {
                                        echo "<p>All Branches</p>";
                                    }
                                ?>
                                <a href="reports.php"><img src="../resources/img/icons/see_more_3.png" alt="see_more">See more</a>
                            </div>
                        </div> 
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
                                <div class="search_cont">
                                    <input type="text" placeholder="Search">
                                    <img src="../resources/img/icons/search.png" alt="search">
                                </div>
                                <?php
                                    $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                    $where = [];
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
                                                <tr style='height: 35vh; border: none; cursor: auto;'>
                                                    <td rowspan='5' colspan='7' class='no_records_found'> 
                                                        <br>
                                                        <img src=\"../resources/img/icons/no_record_big.png\" alt\"no_records_found\">
                                                        <h3 style='font-size: 18px;'>No Upcoming Renewals</h3>
                                                        <br>
                                                        <p style='font-size: 15px; opacity: 0.85;'>You do not have pawned items nearing a due date.</p>
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