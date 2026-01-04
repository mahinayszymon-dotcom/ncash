<?php
    if ($_SESSION['branch_id'] == 1100) {
        $branch_name = 'Marikina-Pasig City Branch';
    } else if ($_SESSION['branch_id'] == 1101) {
        $branch_name = 'Quezon City Branch';
    } else if ($_SESSION['branch_id'] == 1102) {
        $branch_name = 'Makati City Branch';
    } else {
        $_SESSION['report_error_msg'] = 'Error: Cannot retrieve branch ID.';
        $style = 'style="display: none;"';
    }
?>
<div class="branch-card-a" <?php if (isset($style)) { echo $style; }?>>
    <div class="card-header">
        <div class="branch-ident">
            <h3><?php echo $branch_name; ?></h3>
        </div>
    </div>
    <div class="top_analytics">
        <div class="top_analytics_title">
            <p>TOTAL PRINCIPAL OUTSTANDING</p>
        </div>
        <div class="top_analytics_value">
            <?php
                $role = $_SESSION['role'];
                $branch_id = $_SESSION['branch_id'];

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
                    $trend_output_principal = "+ " . round($percent_change_prin, 2) . " %";
                    $trend_class = "trend_up"; 
                } elseif ($percent_change_prin < 0) {
                    $trend_output_principal = "- " . round(abs($percent_change_prin), 2) . " %";
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
    </div>
    <div class="more_analytics">
        <div class="info"><span class="circle_purple"></span>Total Pawns</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS active_count_mp FROM inventory WHERE status = 'Active' AND branch_id = 1100";

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
                $active_count_mp = $row['active_count_mp'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> -- </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Redemptions</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS redeem_count_mp FROM inventory WHERE status = 'Redeemed' AND branch_id = 1100";

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
                $redeem_count_mp = $row['redeem_count_mp'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> -- </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Renewed</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS overdue_count_mp FROM inventory WHERE status = 'Overdue' AND branch_id = 1100";

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
                $overdue_count_mp = $row['overdue_count_mp'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> -- </p>";
            ?>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="val">
            <p>DEBIT</p>
            <p>₱ 0.00</p>
        </div>
        <div class="val">
            <p>CREDIT</p>
            <p>₱ 0.00</p>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="end_bal">
            <p>END BALANCE</p>
            <p>₱ 0.00</p>
        </div>
    </div>
    <div class="available_reports_cont">
        <button style="display: none;"><img src="../resources/img/icons/more_p.png" alt="see_more">View detailed report</button>
        <button><img src="../resources/img/icons/download_w.png" alt="download">Download Weekly Report</button>
    </div>
</div>
<div class="branch-card-a">
    <div class="card-header">
        <div class="branch-ident"> 
            <h3>Your Branch Analytics</h3>
        </div>
    </div>
    <div class="more_analytics">
        <div class="info"><span class="circle_green"></span>Active Items</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS active_count_mp FROM inventory WHERE status = 'Active' AND branch_id = 1100";

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
                $active_count_mp = $row['active_count_mp'];
                
                echo "<p style=\"background-color: #d9e7ddff !important; color: #547f57ff !important;\"> $active_count_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_blue"></span>Redeemed Items</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS redeem_count_mp FROM inventory WHERE status = 'Redeemed' AND branch_id = 1100";

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
                $redeem_count_mp = $row['redeem_count_mp'];
                
                echo "<p style=\"background-color: #d5dae4ff !important; color: #5864a0ff !important;\"> $redeem_count_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_red"></span>Overdue Items</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS overdue_count_mp FROM inventory WHERE status = 'Overdue' AND branch_id = 1100";

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
                $overdue_count_mp = $row['overdue_count_mp'];
                
                echo "<p style=\"background-color: #edd9d9 !important; color: #985d5dff !important;\"> $overdue_count_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Transactions Recorded</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(*) AS transact_count_mp FROM transactions";

                if($role != 'admin')
                {
                    $sql .= " WHERE branch_id = ?";
                }

                $stmt = $conn->prepare($sql);

                if($role != 'admin')
                {
                    $stmt->bind_param("i", $branch_id);
                }

                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $transact_count_mp = $row['transact_count_mp'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> $transact_count_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Clients</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(DISTINCT client_id) AS total_clients FROM inventory";

                if($role != 'admin')
                {
                    $sql .= " WHERE branch_id = ?";
                }

                $stmt = $conn->prepare($sql);

                if($role != 'admin')
                {
                    $stmt->bind_param("i", $branch_id);
                }

                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_clients = $row['total_clients'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> $total_clients </p>";
            ?>
        </div>
        <div class="info"><span class="circle_purple"></span>Notifications Sent</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT COUNT(DISTINCT client_id) AS total_clients_notified FROM notifs";

                if($role != 'admin')
                {
                    $sql .= " WHERE status = 'Sent' AND branch_id = ?";
                }

                $stmt = $conn->prepare($sql);

                if($role != 'admin')
                {
                    $stmt->bind_param("i", $branch_id);
                }

                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_clients_notified = $row['total_clients_notified'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> $total_clients_notified </p>";
            ?>
        </div>
    </div>
</div>
<div class="branch-card-a">
    <div class="card-header">
        <div class="branch-ident"> 
            <h3>Detailed Report</h3>
        </div>
    </div>
</div>