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