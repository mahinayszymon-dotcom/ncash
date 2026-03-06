<?php
include("../config/session_check.php");
include("../config/db_conn.php");
include("../db/branch_fetch.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Message Logs</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/message_logs.css">
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
            <div class="central_panelC">
                <div class="data_tableC">
                    <div class="data_panel_header">
                        <div class="data_panel_name">
                            <div class="icon_normal">
                                <img src="../resources/img/icons/table_w.png" alt="mail">
                            </div>
                            <h2>Messages</h2>
                        </div>
                        <div class="data_panel_buttons">
                            <form action="message_logs.php" method="GET">
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
                                    <option value="sms" <?= $selected === 'sms' ? 'selected' : '' ?>>SMS</option>
                                    <option value="email" <?= $selected === 'email' ? 'selected' : '' ?>>Email</option>
                                    <option value="sent" <?= $selected === 'sent' ? 'selected' : '' ?>>Sent</option>
                                    <option value="failed" <?= $selected === 'failed' ? 'selected' : '' ?>>Failed</option>
                                    <option value="queued" <?= $selected === 'queued' ? 'selected' : '' ?>>Queued</option>
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
                                    //
                                }

                                $role = $_SESSION['role'];
    
                                $sql = "SELECT n.notif_id, c.fullname, n.message, n.type, n.status, n.date_sent
                                        FROM notifs AS n
                                        INNER JOIN clients AS c ON n.client_id = c.client_id";

                                $count_sql = "SELECT COUNT(*) AS total
                                                FROM notifs AS n
                                                INNER JOIN clients AS c ON n.client_id = c.client_id";
                                    
                                if($role != 'admin')
                                {
                                    $sql .= " WHERE n.branch_id = ?";
                                }
            
                                $stmt = $conn->prepare($sql);
                                $count_stmt = $conn->prepare($count_sql);
            
                                if($role != 'admin')
                                {
                                    $stmt->bind_param("i", $branch_id);
                                }

                                // count
                                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                $limit = 12;
                                $offset = ($page - 1) * $limit;
            
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
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Destination</th>
                                    <th>Message ID</th>
                                    <th>Date Sent</th>
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
                                            $notif_id = htmlspecialchars($row['notif_id']);
                                            $client_name = htmlspecialchars($row['fullname']);
                                            $message = htmlspecialchars($row['message']);
                                            $type = htmlspecialchars($row['type']);
                                            $status = htmlspecialchars($row['status']);
                                            $date_sent = htmlspecialchars($row['date_sent']);
        
                                            $format_date = date("j M Y", strtotime($date_sent));
        
                                            $status_style = "font-size: 15px;";
                                            $type_style = "font-size: 15px;";

                                                if ($type == 'SMS' || $type == 'Email') {
                                                    $type_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400; padding: 5px 8px; border-radius: 5px; background-color: var(--main-background-data); border: 2px solid var(--main-background-data); color: var(--black-dark) !important;";
                                                } else {
                                                    $type_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; border: 2px solid var(--nav-unfocused); color: var(--main-background-data);";
                                                }

                                                if ($status == 'Sent') {
                                                    $status_style .= "border: 2px solid #d2e8ce; display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400; padding: 5px 8px; border-radius: 5px; background-color: #d2e8ce; color: #688e5b;";
                                                } else if ($status == 'Failed') {
                                                    $status_style .= "border: 2px solid #e9cbcb; display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #e9cbcb; color: #915656;";
                                                } else {
                                                    $status_style .= "border: 2px solid #f1eceb; display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: var(--main-background-data) !important; color: var(--loading-dark);";
                                                }

                                            
                                            echo 
                                            "
                                            <tr>
                                                <td> $number </td>
                                                <td><span style=\"$type_style\">$type</span></td>
                                                <td><span style=\"$status_style\">$status</span></td>
                                                <td> $client_name </td>
                                                <td> $notif_id </td>
                                                <td> $format_date </td>
                                                <td>
                                                    <a href=\"../dashboard/message_logs/details.php?id=" . $notif_id . "\"><button type=\"submit\"><img src=\"../resources/img/icons/open.png\" alt=\"open\"></button></a>
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
                                                <td rowspan='1' colspan='7' style='text-align: center; vertical-align: middle; height: 150px; font-weight: 600;'> No records found </td>
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
                                    <button onclick="window.location.href='../dashboard/transactions.php'"><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                    <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </section>
    </section>
</body>
</html>
