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
                                <option value="datetimeold" <?= $selected === 'datetimeold' ? 'selected' : '' ?>>Date & Time (Old)</option>
                                <option value="datetimenew" <?= $selected === 'datetimenew' ? 'selected' : '' ?>>Date & Time (New)</option>
                                <option value="accessed" <?= $selected === 'accessed' ? 'selected' : '' ?>>Action: Accessed</option>
                                <option value="created" <?= $selected === 'created' ? 'selected' : '' ?>>Action: Created</option>
                                <option value="edited" <?= $selected === 'edited' ? 'selected' : '' ?>>Action: Edited</option>
                                <option value="archive" <?= $selected === 'archive' ? 'selected' : '' ?>>Action: Archive</option>
                                <option value="deleted" <?= $selected === 'deleted' ? 'selected' : '' ?>>Action: Deleted</option>
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
                                    $orderBy = " ORDER BY tr.timestamp DESC";
                                    break;
                                case 'pasig':
                                     $where[] = "b.branch_name = 'Marikina-Pasig'";
                                     $orderBy = " ORDER BY tr.timestamp DESC";
                                     break;
                                case 'quezon':
                                     $where[] = "b.branch_name = 'Quezon City'";
                                     $orderBy = " ORDER BY tr.timestamp DESC";
                                     break;
                                case 'makati': 
                                     $where[] = "b.branch_name = 'Makati'";
                                     $orderBy = " ORDER BY tr.timestamp DESC";
                                     break;
                                case 'nameAZ': 
                                    $orderBy = " ORDER BY u.username ASC";
                                    break;
                                case 'nameZA': 
                                    $orderBy = " ORDER BY u.username DESC";
                                    break;
                                case 'datetimeold': 
                                     $orderBy = " ORDER BY tr.timestamp ASC";
                                     break;
                                case 'datetimenew': 
                                     $orderBy = " ORDER BY tr.timestamp DESC";
                                     break;
                                case 'accessed':
                                     $where[] = "tr.action = 'Accessed'";
                                     break;
                                case 'created':
                                     $where[] = "tr.action = 'Created'";
                                     break;
                                case 'edited':
                                     $where[] = "tr.action = 'Edited'";
                                     break;
                                case 'archive':
                                     $where[] = "tr.action = 'Archive'";
                                     break;
                                case 'deleted':
                                     $where[] = "tr.action = 'Deleted'";
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
                            $limit = 9;
                            $offset = ($page - 1) * $limit;

                            $sql = "SELECT u.fullname, u.branch_id, tr.action, tr.object_type, tr.description, b.branch_name, tr.timestamp
                                    FROM audit_trail AS tr
                                    INNER JOIN branches AS b ON tr.branch_id = b.branch_id
                                    INNER JOIN users AS u ON tr.user_id = u.user_id
                                    $where_sql
                                    $orderBy
                                    LIMIT $limit OFFSET $offset";

                            $count_sql = "SELECT COUNT(*) AS total
                                FROM audit_trail AS tr
                                INNER JOIN branches AS b ON tr.branch_id = b.branch_id
                                INNER JOIN users AS u ON tr.user_id = u.user_id
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
                                <th>User</th>
                                <th>Audit Log</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $number = (($page - 1) * $limit) + 1;

                            if($result->num_rows > 0) 
                            {
                                while($row = $result->fetch_assoc())
                                {
                                    $audit_name = htmlspecialchars($row['fullname']);
                                    $action = htmlspecialchars($row['action']);
                                    $obj_type = htmlspecialchars($row['object_type']);
                                    $desc = htmlspecialchars($row['description']);
                                    $aud_branch = htmlspecialchars($row['branch_name']);
                                    $u_branch = htmlspecialchars($row['branch_id']);
                                    $timestamp = htmlspecialchars($row['timestamp']);
    
                                    if ($u_branch == 1100) {
                                        $branch_u = "Marikina-Pasig";
                                    } else if ($u_branch == 1101) {
                                        $branch_u = "Quezon City";
                                    } else if ($u_branch == 1102) {
                                        $branch_u = "Makati City";
                                    } else {
                                        $branch_u = "-";
                                    }

                                    if ($aud_branch === "Marikina-Pasig") {
                                        $branch_acro = "Marikina-Pasig";
                                    } else if ($aud_branch === "Quezon City") {
                                        $branch_acro = "Quezon City";
                                    } else if ($aud_branch === "Makati") {
                                        $branch_acro = "Makati City";
                                    } else {
                                        $branch_acro = "-";
                                    }

                                    $format_date = date("M d, Y | h:i A", strtotime($timestamp));

                                    echo 
                                    "
                                    <tr>
                                        <td> 
                                            <div class=\"audit_profile_circle\">
                                                " . ucwords(mb_substr($audit_name, 0, 1)) . "
                                            </div>
                                            <div class=\"audit_content\">
                                                <h3>$audit_name</h3>
                                                <p>$branch_u</p>
                                            </div>
                                        </td>
                                        <td> 
                                            <div class=\"audit_content\">
                                                <h3>$action $obj_type</h3>
                                                <p>$desc on $branch_acro branch.</p>
                                            </div>
                                        </td>
                                        <td> $format_date</td>
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
                            include("../../includes/pagination.php")
                        ?>
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