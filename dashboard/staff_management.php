<?php
include("../config/session_check.php");
include("../config/db_conn.php");
include("../db/branch_fetch.php");

if ($role !== "admin") {
    header("Location: ../../auth/denied.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/staff_management.css">
</head>
<body>
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
            <div class="central_panel">
                <div class="data_tableB">
                    <div class="data_panel_header">
                        <div class="data_panel_name">
                            <div class="icon_normal">
                                <img src="../resources/img/icons/user_w.png" alt="users">
                            </div>
                            <h2>Employees Data Tabulation</h2>
                        </div>
                        <div class="data_panel_buttons">
                            <?php
                                $role = $_SESSION['role'];
    
                                $sql = "SELECT u.user_id, u.username, u.fullname, u.email, u.role, b.branch_name, u.status
                                        FROM users AS u
                                        INNER JOIN branches AS b on u.branch_id = b.branch_id";
            
                                $result = $conn->query($sql);
                                $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                $where = [];
                                $orderBy = '';
                                $where_sql = '';

                                switch ($sorting)
                                {
                                    case 'pasig':
                                        $where[] = "b.branch_name = 'Marikina-Pasig'";
                                        break;
                                    case 'quezon':
                                        $where[] = "b.branch_name = 'Quezon City'";
                                        break;
                                    case 'makati': 
                                        $where[] = "b.branch_name = 'Makati'";
                                        break;
                                    case 'nameAZ': 
                                        $orderBy = " ORDER BY u.fullname ASC"; 
                                        break;
                                    case 'nameZA': 
                                        $orderBy = " ORDER BY u.fullname DESC"; 
                                        break;
                                    case 'admin': 
                                        $where[] = "u.role = 'admin'"; 
                                        break;
                                    case 'users': 
                                        $where[] = "u.role = 'user'"; 
                                        break;
                                    case 'active':
                                        $where[] = "u.status = 'Active'";
                                        break;
                                    case 'inactive':
                                        $where[] = "u.status = 'Inactive'";
                                        break;
                                    case 'default':
                                    default:
                                        $orderBy = " ORDER BY u.user_id ASC"; // Changed due_date to user_id
                                        break;
                                } 

                                if (!empty($where)) 
                                {
                                    $where_sql = " WHERE " . implode(" AND ", $where);;
                                }

                                /*Count*/
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $limit = 8;
                                $offset = ($page - 1) * $limit;

                                $base_sql = " FROM users AS u INNER JOIN branches AS b ON u.branch_id = b.branch_id";
                                                
                                $sql = "SELECT u.user_id, u.username, u.fullname, u.email, u.role, b.branch_name, u.status " 
                                        . $base_sql 
                                        . $where_sql 
                                        . $orderBy 
                                        . " LIMIT " . $limit . " OFFSET " . $offset;
                                    
                                $count_sql = "SELECT COUNT(*) AS total "
                                        . $base_sql
                                        . $where_sql;
                                
                                $stmt = $conn->prepare($sql);
                                $count_stmt = $conn->prepare($count_sql);   
    
                                $sql .= $orderBy;
                                $sql .= " LIMIT 7"; // Ni-limit ko muna sa 7

                                $stmt->execute();
                                $result = $stmt->get_result();
                                $count_stmt->execute();
                                $count_result = $count_stmt->get_result();
                                $total_row = $count_result ? $count_result->fetch_assoc() : null;
                                $total = $total_row['total'] ?? 0;
                            ?>
                            <form action="staff_management.php" method="GET">
                                <span class="custom-arrow-sort"><img src="../resources/img/icons/filter.png" alt="filter"></span>
                                <select name="branch" id="branch" onchange="this.form.submit()" class="sort">
                                    <?php
                                        $selected = $_GET['branch'] ?? 'all'; 
    
                                            echo "
                                            <option value='all' " . ($selected === 'all' ? 'selected' : '') . ">All Branch</option>
                                            <option value='pasig' " . ($selected === 'pasig' ? 'selected' : '') . ">Pasig City Branch</option>
                                            <option value='quezon' " . ($selected === 'quezon' ? 'selected' : '') . ">Quezon City Branch</option>
                                            <option value='makati' " . ($selected === 'makati' ? 'selected' : '') . ">Makati City Branch</option>
                                            ";
                                        
                                    ?>
                                    <option value="nameAZ" <?= $selected === 'nameAZ' ? 'selected' : '' ?>>Name (A-Z)</option>
                                    <option value="nameZA" <?= $selected === 'nameZA' ? 'selected' : '' ?>>Name (Z-A)</option>
                                    <option value="admin" <?= $selected === 'admin' ? 'selected' : '' ?>>Administrators</option>
                                    <option value="users" <?= $selected === 'users' ? 'selected' : '' ?>>Users</option>
                                    <option value="active" <?= $selected === 'active' ? 'selected' : '' ?>>Active Users</option>
                                    <option value="inactive" <?= $selected === 'inactive' ? 'selected' : '' ?>>Inactive Users</option>
                                </select>
                                <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                            </form>
                        </div>
                    </div>
                    <div class="table_cont">
                        <table border="1" cellspacing="0" cellpadding="8">
                            <tbody>
                                <?php
                                    if($result->num_rows > 0) 
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            $u_user_ID = htmlspecialchars($row['user_id']);
                                            $u_username = htmlspecialchars($row['username']);
                                            $u_fullname = htmlspecialchars($row['fullname']);
                                            $u_email = htmlspecialchars($row['email']);
                                            $u_role = htmlspecialchars($row['role']);
                                            $u_branch = htmlspecialchars($row['branch_name']);
                                            $u_status = htmlspecialchars($row['status']);
        
                                            $profile_char = mb_substr($u_fullname, 0, 1);
                                            
                                            if($u_status === 'active') {
                                                $state = 'active';
                                            } else {
                                                $state = 'inactive';
                                            }

                                            if($u_role === 'admin') {
                                                $privelage = 'admin';
                                            } else {
                                                $privelage = 'user';
                                            }

                                            echo
                                            "
                                            <tr>
                                                <div class='user_card'>
                                                    <div class='user_profile'>
                                                        <div class='user_profile_icon'>
                                                            <p>$profile_char</p>
                                                            <span class='u_status $state'></span>
                                                        </div>
                                                        <div class='user_profile_name'>
                                                            <p>$u_fullname</p>
                                                            <p>$u_email</p>
                                                        </div>
                                                    </div>
                                                    <div class='user_info'>
                                                        <div class='info $privelage'>
                                                            " . ucwords($u_role) . "
                                                        </div>
                                                        <div class='info'>
                                                            $u_branch
                                                        </div>
                                                    </div>
                                                    <div class='user_actions'>
                                                        <a href=\"../dashboard/staff/info.php?id=" . $u_user_ID . "\"><button type=\"submit\"><img src=\"../resources/img/icons/open.png\" alt=\"open\"></button></a>
                                                    </div>
                                                <div>
                                            </tr>
                                            ";
                                        }
                                    }
                                    else
                                    {
                                        echo
                                        "
                                            <tr>
                                                <td rowspan='5' colspan='7' style='text-align: center; vertical-align: middle; height: 150px; font-weight: 600;'> No records found </td>
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
                                <button onclick="window.location.href='staff_management.php'"><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                            
                            </div>
                        </div>
                    </div>
                </div>
                <div class="data_controls">
                    <div class="data_controls_header">
                        <h2>Add New User</h2>
                        <div class="icon_normal">
                            <img src="../resources/img/icons/add_user_w.png" alt="add_user">
                        </div>
                    </div>
                    <div class="data_controls_form">

                    </div>
                </div>
            </div>
        </section>
    </section>
</body>
</html>