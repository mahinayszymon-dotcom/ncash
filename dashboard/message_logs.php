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
    <title>Notifications</title>
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
            <div class="central_panel">
                <div class="data_table">
                    <?php
                        $role = $_SESSION['role'];
    
                        $sql = "SELECT n.notif_id, c.fullname, n.message, n.type, n.status, n.date_sent
                                FROM notifs AS n
                                INNER JOIN clients AS c ON n.client_id = c.client_id";
                            
                        if($role != 'admin')
                        {
                            $sql .= " WHERE n.branch_id = ?";
                        }
    
                        $stmt = $conn->prepare($sql);
    
                        if($role != 'admin')
                        {
                            $stmt->bind_param("i", $branch_id);
                        }
    
                        $stmt->execute();
                            
                        $result = $stmt->get_result();
                    ?>
                    <table border="1" cellspacing="0" cellpadding="8">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Client Name</th>
                                <th>Message</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
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
    
                                        $format_date = date("M j, Y", strtotime($date_sent));
    
                                        echo 
                                        "
                                        <tr>
                                            <td> $notif_id </td>
                                            <td> $client_name </td>
                                            <td> $message </td>
                                            <td> $type </td>
                                            <td> $status </td>
                                            <td> $format_date </td>
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
                <div class="data_controls">
                    
                </div>
            </div>
        </section>
    </section>
</body>
</html>
