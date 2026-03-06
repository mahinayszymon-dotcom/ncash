<?php
include("../../config/session_check.php");
include("../../config/db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail</title>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/transactions.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/logs.css">
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
</head>
<body>
    <section class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <div class="top_panel">
                <div class="top_panel_content">
                    <div class="text_cont">
                        <h1>Audit Trail</h1>
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
            <div class="data_tableC">
                <div class="data_panel_header">
                    <div class="data_panel_name">
                        <div class="icon_normal">
                            <img src="../../resources/img/icons/table_w.png" alt="table_icon">
                        </div>
                        <h2>Data Tabulation</h2>
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
                        <form action="audit_trail.php" method="GET">
                            <span class="custom-arrow-sort"><img src="../../resources/img/icons/filter.png" alt="filter"></span>
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
                                <option value="nameZA" <?= $selected === 'datetimeold' ? 'selected' : '' ?>>Date & Time (Old)</option>
                                <option value="nameZA" <?= $selected === 'datetimenew' ? 'selected' : '' ?>>Date & Time (New)</option>
                                <option value="nameZA" <?= $selected === 'groupedactions' ? 'selected' : '' ?>>Grouped Actions</option>
                            </select>
                            <span class="custom-arrow"><img src="../../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
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
                                    $orderBy = " ORDER BY t.created_at DESC";
                                    break;
                                // case 'pasig':
                                //     $where[] = "b.branch_name = 'Marikina-Pasig'";
                                //     $orderBy = " ORDER BY t.edited_at DESC";
                                //     break;
                                // case 'quezon':
                                //     $where[] = "b.branch_name = 'Quezon City'";
                                //     $orderBy = " ORDER BY t.edited_at DESC";
                                //     break;
                                // case 'makati': 
                                //     $where[] = "b.branch_name = 'Makati'";
                                //     $orderBy = " ORDER BY t.edited_at DESC";
                                //     break;
                                case 'nameAZ': 
                                    $orderBy = " ORDER BY c.fullname ASC";
                                    break;
                                case 'nameZA': 
                                    $orderBy = " ORDER BY c.fullname DESC";
                                    break;
                                // case 'price_increasing': 
                                //     $orderBy = " ORDER BY t.amount ASC";
                                //     break;
                                // case 'price_decreasing': 
                                //     $orderBy = " ORDER BY t.amount DESC";
                                //     break;
                                // case 'renewal':
                                //     $where[] = "t.type_of_pay = 'Interest'";
                                //     break;
                                // case 'redeem':
                                //     $where[] = "t.type_of_pay = 'Principal'";
                                //     break;
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
                            $limit = 12;
                            $offset = ($page - 1) * $limit;

                            $sql = "SELECT t.transaction_id, t.agreement_num, c.fullname, i.item_name, i.principal, b.branch_name, t.amount, t.type_of_pay, t.method
                                FROM transactions AS t
                                INNER JOIN clients AS c ON t.client_id = c.client_id
                                INNER JOIN inventory AS i ON t.item_id = i.item_id
                                INNER JOIN branches AS b ON t.branch_id = b.branch_id
                                $where_sql
                                $orderBy
                                LIMIT $limit OFFSET $offset";

                            $count_sql = "SELECT COUNT(*) AS total
                                FROM transactions AS t
                                INNER JOIN clients AS c ON t.client_id = c.client_id
                                INNER JOIN inventory AS i ON t.item_id = i.item_id
                                INNER JOIN branches AS b ON t.branch_id = b.branch_id
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
                    <table id="audit_trail">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Action Type</th>
                                <th>Object Type</th>
                                <th>Description</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $number = (($page - 1) * $limit) + 1;

                            // if($result->num_rows > 0) 
                            // {
                            //     while($row = $result->fetch_assoc())
                            //     {
                            //         $transaction_id = htmlspecialchars($row['transaction_id']);
                            //         $agreement_num = htmlspecialchars($row['agreement_num']);
                            //         $client_name = htmlspecialchars($row['fullname']);
                            //         $item = htmlspecialchars($row['item_name']);
                            //         $item_principal = htmlspecialchars($row['principal']);
                            //         $branch = htmlspecialchars($row['branch_name']);
                            //         $amount = htmlspecialchars($row['amount']);
                            //         $pay_type = htmlspecialchars($row['type_of_pay']);
                            //         $method = htmlspecialchars($row['method']);

                            //         $principal_decimal = number_format($item_principal, 2);
                            //         $amount_decimal = number_format($amount, 2);
    
                            //         if ($branch === "Marikina-Pasig") {
                            //             $final_agreement_num = "MP" . $agreement_num;
                            //         } else if ($branch === "Quezon City") {
                            //             $final_agreement_num = "Q" . $agreement_num;
                            //         } else if ($branch === "Makati") {
                            //             $final_agreement_num = "M" . $agreement_num;
                            //         } else {
                            //             $final_agreement_num = "-" . $agreement_num;
                            //         }

                            //         echo 
                            //         "
                            //         <tr>
                            //             <td> $number </td>
                            //             <td> $final_agreement_num </td>
                            //             <td> $client_name </td>
                            //             <td> $item </td>
                            //             <td>₱ $principal_decimal</td>
                            //             <td>₱ $amount_decimal</td>
                            //         </tr>
                            //         ";

                            //         $number++;
                            //     }
                            // }
                            // else
                            // {
                            //     echo
                            //         "
                            //             <tr style='height: auto; border: none; cursor: auto;'>
                            //                 <td rowspan='5' colspan='7' class='no_records_found'> 
                            //                     <br>
                            //                     <img src=\"../resources/img/icons/no_record_big.png\" alt\"no_records_found\">
                            //                     <h3 style='font-size: 18px;'>No Records Found</h3>
                            //                     <br>
                            //                     <p style='font-size: 15px; opacity: 0.85;'>Try searching a different category or create a new data.</p>
                            //                     <br>
                            //                 </td>
                            //             </tr>
                            //         ";
                            // }
                        ?>
                            <tr>
                                <td>1</td>
                                <td>
c
                                </td>
                                <td>CREATE</td>
                                <td>Item</td>
                                <td>Created item 'Sample'</td>
                                <td>01 Mar 2026</td>
                            </tr>
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
                            <button onclick="window.location.href='../dashboard/transactions.php'"><img src="../../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                            <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</body>
</html>