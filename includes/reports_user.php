<?php
    if ($_SESSION['branch_id'] == 1100) {
        $branch_name = 'Marikina-Pasig';
    } else if ($_SESSION['branch_id'] == 1101) {
        $branch_name = 'Quezon City';
    } else if ($_SESSION['branch_id'] == 1102) {
        $branch_name = 'Makati City';
    } else {
        $_SESSION['report_error_msg'] = 'Error: Cannot retrieve branch ID.';
        $style = 'style="display: none;"';
    }
?>
<div class="branch-card-a" <?php if (isset($style)) { echo $style; }?>>
    <div class="card-header">
        <div class="branch-ident">
            <h3><?php echo $branch_name; ?> Branch</h3>
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
                $sql = "SELECT SUM(principal) AS total_pawn FROM inventory 
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND branch_id = ?
                        AND is_omitted != 1";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_pawn = $row['total_pawn'];
                $pawn_decimal = number_format($total_pawn, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $pawn_decimal </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Redemptions</div>
        <div class="value">
            <?php
                $sql = "SELECT SUM(amount) AS total_redeem FROM transactions 
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND type_of_pay = 'Principal'
                        AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_redeem = $row['total_redeem'];
                $redeem_decimal = number_format($total_redeem, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $redeem_decimal </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Interest</div>
        <div class="value">
            <?php
                $sql = "SELECT SUM(amount) AS total_int FROM transactions 
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND type_of_pay = 'Interest'
                        AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_int = $row['total_int'];
                $int_decimal = number_format($total_int, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $int_decimal </p>";
            ?>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="val">
            <p>DEBIT</p>
            <?php
                $sql = "SELECT SUM(i.principal) AS total_renew FROM inventory AS i
                        INNER JOIN transactions AS t ON i.item_id = t.item_id
                        WHERE t.created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND t.created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND t.type_of_pay = 'Interest'
                        AND t.branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_renew = $row['total_renew'];
                
                $sql = "SELECT SUM(amount) AS debit_transacs FROM transactions
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND method != 'Cash'
                        AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $debit_transacs = $row['debit_transacs'];

                $sql = "SELECT SUM(amount) AS debit_eb_transacs FROM eb_transactions
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND type_of_transac = 'Debit'
                        AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $debit_eb_transacs = $row['debit_eb_transacs'];

                $total_debit = $total_pawn + $total_renew + $debit_transacs + $debit_eb_transacs;
                $debit_decimal = number_format($total_debit, 2);

                echo "<p>₱ $debit_decimal </p>";
            ?>
        </div>
        <div class="val">
            <p>CREDIT</p>
            <?php 
                $sql = "SELECT SUM(interest) AS adv_int FROM inventory 
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND branch_id = ?
                        AND is_omitted != 1";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $adv_int = $row['adv_int'];

                $sql = "SELECT SUM(amount) AS credit_eb_transacs FROM eb_transactions
                        WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                        AND created_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
                        AND type_of_transac = 'Credit'
                        AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $credit_eb_transacs = $row['credit_eb_transacs'];

                $total_credit = $total_redeem + $total_renew + $adv_int + $total_int + $credit_eb_transacs;
                $credit_decimal = number_format($total_credit, 2);

                echo "<p>₱ $credit_decimal </p>";
            ?>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="end_bal">
            <p>END BALANCE</p>
            <?php 
                $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();

                $final_eb = htmlspecialchars($row['end_balance']);
                $final_eb = number_format($final_eb, 2);

                echo "<p>₱ $final_eb</p>"
            ?>
        </div>
    </div>
    <div class="available_reports_cont">
        <button style="display: none;"><img src="../resources/img/icons/more_p.png" alt="see_more">View detailed report</button>
        <button onclick="downloadPdf('<?php echo $branch_name; ?>', <?php echo $branch_id; ?>)"><img src="../resources/img/icons/download_w.png" alt="download">Download Monthly Report</button>
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
                $sql = "SELECT COUNT(*) AS active_count FROM inventory WHERE status = 'Active' AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $active_count = $row['active_count'];
                
                echo "<p style=\"background-color: #d9e7ddff !important; color: #547f57ff !important;\"> $active_count </p>";
            ?>
        </div>

        <div class="info"><span class="circle_blue"></span>Redeemed Items</div>
        <div class="value">
            <?php
                $sql = "SELECT COUNT(*) AS redeem_count FROM inventory WHERE status = 'Redeemed' AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $redeem_count = $row['redeem_count'];
                
                echo "<p style=\"background-color: #d5dae4ff !important; color: #5864a0ff !important;\"> $redeem_count </p>";
            ?>
        </div>

        <div class="info"><span class="circle_red"></span>Overdue Items</div>
        <div class="value">
            <?php
                $sql = "SELECT COUNT(*) AS overdue_count FROM inventory WHERE status = 'Overdue' AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);                
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $overdue_count = $row['overdue_count'];
                
                echo "<p style=\"background-color: #edd9d9 !important; color: #985d5dff !important;\"> $overdue_count </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Transactions Recorded</div>
        <div class="value">
            <?php
                $sql = "SELECT COUNT(*) AS transact_count FROM transactions WHERE branch_id = ?";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $transact_count = $row['transact_count'];
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\"> $transact_count </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Clients</div>
        <div class="value">
            <?php
                $sql = "SELECT COUNT(DISTINCT client_id) AS total_clients FROM inventory WHERE branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
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
                $sql = "SELECT COUNT(DISTINCT client_id) AS total_clients_notified FROM notifs WHERE status = 'Sent' AND branch_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $branch_id);
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