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
            if (!function_exists('calculate_trend_active')) {
                function calculate_trend_active($current, $previous) {
                    if ($previous == 0) {
                        return $current > 0 ? 100 : 0; 
                    }
                    return (($current - $previous) / $previous) * 100;
                }
            }

            $role = $_SESSION['role'];

            $sql = "SELECT COUNT(*) AS active_count FROM inventory WHERE status = 'Active'";
            if($role != 'admin') { $sql .= " AND branch_id = ?"; }

            $stmt = $conn->prepare($sql);
            if($role != 'admin') { $stmt->bind_param("i", $branch_id); }

            $stmt->execute();    
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $active_count = $row['active_count'];

            $current_active_trend = $active_count;
            $prev_active_trend = 0;

            // Current Month
            $sql_current = "SELECT COUNT(*) AS curr_active FROM inventory WHERE status = 'Active'";
            if($role != 'admin') { $sql_current .= " AND branch_id = ?"; }
            $sql_current .= " AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')";
            
            $stmt_curr = $conn->prepare($sql_current);
            if($role != 'admin') { $stmt_curr->bind_param("i", $branch_id); }
            if ($stmt_curr && $stmt_curr->execute()) {
                $current_active_trend = $stmt_curr->get_result()->fetch_assoc()['curr_active'] ?? 0;
            }

            // Previous Month (Cleaned)
            if ($role == 'admin') {
                $sql_prev = "SELECT SUM(prev_active_count) AS prev_active FROM branches";
                $stmt_prev = $conn->prepare($sql_prev);
            } else {
                $sql_prev = "SELECT prev_active_count AS prev_active FROM branches WHERE branch_id = ?";
                $stmt_prev = $conn->prepare($sql_prev);
                $stmt_prev->bind_param("i", $branch_id);
            }
            if ($stmt_prev && $stmt_prev->execute()) {
                $prev_active_trend = $stmt_prev->get_result()->fetch_assoc()['prev_active'] ?? 0;
            }

            // --- 3. CALCULATE & OUTPUT ---
            $percent_change_active = calculate_trend_active($current_active_trend, $prev_active_trend);
            
            $trend_output_active = "0 %";
            $trend_class = "trend_unknown";

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
            if($role != 'admin') {
                echo "<p>In $user_branch Branch</p>";
            } else if($role == 'admin') {
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
            if (!function_exists('calculate_trend_redeemed')) {
                function calculate_trend_redeemed($current, $previous) {
                    if ($previous <= 0) {
                        return $current > 0 ? 100 : 0; 
                    }
                    return (($current - $previous) / $previous) * 100;
                }
            }

            $sql = "SELECT COUNT(*) AS redeem_count FROM inventory WHERE status = 'Redeemed'";
            if($role != 'admin') { $sql .= " AND branch_id = ?"; }

            $sql .= " AND updated_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') 
                      AND updated_at <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')";
            $stmt = $conn->prepare($sql);

            if($role != 'admin') { $stmt->bind_param("i", $branch_id); }

            $stmt->execute();    
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $redeem_count = $row['redeem_count'];

            $current_redeem_count = $redeem_count;
            $prev_redeem_count = 0;

            // Previous Month (Cleaned)
            if ($role == 'admin') {
                $sql_prev = "SELECT SUM(prev_redeem_count) AS prev_redeem FROM branches";
                $stmt_prev = $conn->prepare($sql_prev);
            } else {
                $sql_prev = "SELECT prev_redeem_count AS prev_redeem FROM branches WHERE branch_id = ?";
                $stmt_prev = $conn->prepare($sql_prev);
                $stmt_prev->bind_param("i", $branch_id);
            }
            if ($stmt_prev && $stmt_prev->execute()) {
                $prev_redeem_count = $stmt_prev->get_result()->fetch_assoc()['prev_redeem'] ?? 0;
            }

            // --- CALCULATE TREND ---
            $percent_change_redeem = calculate_trend_redeemed($current_redeem_count, $prev_redeem_count);
            
            $trend_output_redeem = "0 %";
            $trend_class = "trend_unknown";

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
            if($role != 'admin') {
                echo "<p>This Month ($user_branch)</p>";
            } else if($role == 'admin') {
                echo "<p>This Month (All Branches)</p>";
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
            if (!function_exists('calculate_trend_overdue')) {
                function calculate_trend_overdue($current, $previous) {
                    if ($previous == 0) {
                        return $current > 0 ? 100 : 0; 
                    }
                    return (($current - $previous) / $previous) * 100;
                }
            }

            $sql = "SELECT COUNT(*) AS over_count FROM inventory WHERE status = 'Overdue'";
            if($role != 'admin') { $sql .= " AND branch_id = ?"; }

            $stmt = $conn->prepare($sql);
            if($role != 'admin') { $stmt->bind_param("i", $branch_id); }

            $stmt->execute();    
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $over_count = $row['over_count'];

            $current_overdue_trend = $over_count;
            $prev_overdue_trend = 0;
            
            $sql_current = "SELECT COUNT(*) AS curr_over FROM inventory WHERE status = 'Overdue'";
            if($role != 'admin') { $sql_current .= " AND branch_id = ?"; }
            $sql_current .= " AND updated_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND updated_at < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')";
            
            $stmt_curr = $conn->prepare($sql_current);
            if($role != 'admin') { $stmt_curr->bind_param("i", $branch_id); }
            if ($stmt_curr && $stmt_curr->execute()) {
                $current_overdue_trend = $stmt_curr->get_result()->fetch_assoc()['curr_over'] ?? 0;
            }

            // Previous Month (Cleaned)
            if ($role == 'admin') {
                $sql_prev = "SELECT SUM(prev_overdue_count) AS prev_over FROM branches";
                $stmt_prev = $conn->prepare($sql_prev);
            } else {
                $sql_prev = "SELECT prev_overdue_count AS prev_over FROM branches WHERE branch_id = ?";
                $stmt_prev = $conn->prepare($sql_prev);
                $stmt_prev->bind_param("i", $branch_id);
            }
            if ($stmt_prev && $stmt_prev->execute()) {
                $prev_overdue_trend = $stmt_prev->get_result()->fetch_assoc()['prev_over'] ?? 0;
            }

            $percent_change_over = calculate_trend_overdue($current_overdue_trend, $prev_overdue_trend);
        
            $trend_output_overdue = "0 %";
            $trend_class = "trend_unknown";

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
            if($role != 'admin') {
                echo "<p>In $user_branch Branch</p>";
            } else if($role == 'admin') {
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
            if (!function_exists('calculate_trend_principal')) {
                function calculate_trend_principal($current, $previous) {
                    if ($previous == 0) {
                        return $current > 0 ? 100 : 0; 
                    }
                    return (($current - $previous) / $previous) * 100;
                }
            }

            $sql = "SELECT SUM(principal) AS total_principal FROM inventory WHERE status != 'Redeemed'";
            if($role != 'admin') { $sql .= " AND branch_id = ?"; }

            $stmt = $conn->prepare($sql);
            if($role != 'admin') { $stmt->bind_param("i", $branch_id); }

            $stmt->execute();    
            $result = $stmt->get_result();

            $row = $result->fetch_assoc();
            $total_principal = $row['total_principal'];
            $formatted_principal = number_format($total_principal, 2);

            $current_prin_trend = $total_principal; // <-- FIXED: Use raw number here, not formatted string
            $prev_prin_trend = 0;

            // Current Month
            $sql_current = "SELECT SUM(principal) AS curr_prin FROM inventory WHERE status != 'Redeemed'";
            if($role != 'admin') { $sql_current .= " AND branch_id = ?"; }
            $sql_current .= " AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')";
            
            $stmt_curr = $conn->prepare($sql_current);
            if($role != 'admin') { $stmt_curr->bind_param("i", $branch_id); }
            if ($stmt_curr && $stmt_curr->execute()) {
                $current_prin_trend = $stmt_curr->get_result()->fetch_assoc()['curr_prin'] ?? 0;
            }

            // Previous Month (Cleaned)
            if ($role == 'admin') {
                $sql_prev = "SELECT SUM(prev_principal) AS prev_prin FROM branches";
                $stmt_prev = $conn->prepare($sql_prev);
            } else {
                $sql_prev = "SELECT prev_principal AS prev_prin FROM branches WHERE branch_id = ?";
                $stmt_prev = $conn->prepare($sql_prev);
                $stmt_prev->bind_param("i", $branch_id);
            }
            if ($stmt_prev && $stmt_prev->execute()) {
                $prev_prin_trend = $stmt_prev->get_result()->fetch_assoc()['prev_prin'] ?? 0;
            }

            $percent_change_prin = calculate_trend_principal($current_prin_trend, $prev_prin_trend);
        
            $trend_output_principal = "0 %";
            $trend_class = "trend_unknown";

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
            if($role != 'admin') {
                echo "<p>In $user_branch Branch</p>";
            } else if($role == 'admin') {
                echo "<p>All Branches</p>";
            }
        ?>
        <a href="reports.php"><img src="../resources/img/icons/see_more_3.png" alt="see_more">See more</a>
    </div>
</div>