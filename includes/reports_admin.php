<div class="branch-card-a">
    <div class="card-header">
        <div class="branch-ident">
            <h3>Marikina-Pasig City Branch</h3>
        </div>
    </div>
    <div class="top_analytics">
        <div class="top_analytics_title">
            <p>TOTAL PRINCIPAL OUTSTANDING</p>
        </div>
        <div class="top_analytics_value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(principal) AS total_principal FROM inventory WHERE status != 'Redeemed' AND branch_id = 1100";

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
        <button onclick="openReport('Marikina-Pasig', 1100)"><img src="../resources/img/icons/more_p.png" alt="see_more">View detailed report</button>
        <button><img src="../resources/img/icons/download_w.png" alt="download">Download Weekly Report</button>
    </div>
</div>
<div class="branch-card-a">
    <div class="card-header">
        <div class="branch-ident"> 
            <h3>Quezon City Branch</h3>
        </div>
    </div>
    <div class="top_analytics">
        <div class="top_analytics_title">
            <p>TOTAL PRINCIPAL OUTSTANDING</p>
        </div>
        <div class="top_analytics_value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(principal) AS total_principal FROM inventory WHERE status != 'Redeemed' AND branch_id = 1101";

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
        <button onclick="openReport('Quezon City', 1101)"><img src="../resources/img/icons/more_p.png" alt="see_more">View detailed report</button>
        <button><img src="../resources/img/icons/download_w.png" alt="download">Download Weekly Report</button>
    </div>
</div>
<div class="branch-card-a">
    <div class="card-header">
        <div class="branch-ident">
            <h3>Makati City Branch</h3>
        </div>
    </div>
    <div class="top_analytics">
        <div class="top_analytics_title">
            <p>TOTAL PRINCIPAL OUTSTANDING</p>
        </div>
        <div class="top_analytics_value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(principal) AS total_principal FROM inventory WHERE status != 'Redeemed' AND branch_id = 1102";

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
                $percent_change_prin = -8; // test

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
        <button onclick="openReport('Makati City', 1102)"><img src="../resources/img/icons/more_p.png" alt="see_more">View detailed report</button>
        <button><img src="../resources/img/icons/download_w.png" alt="download">Download Weekly Report</button>
    </div>
</div>
<script>
    function openReport(branchName, branchId) {
    const modal = document.getElementById('reportModal');
    const title = document.getElementById('modalBranchName');
    const container = document.getElementById('modalDataContainer');

    // Show modal and set loading text
    modal.style.display = 'flex';
    title.innerText = branchName + " Detailed Report";
    container.innerHTML = "<p>Loading live data...</p>";

    // FETCH DATA FROM PHP
    const formData = new FormData();
    formData.append('branch_id', branchId);

    fetch('../db/fetch_branch_data.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        // Inject the HTML from PHP into the modal
        container.innerHTML = html;
    })
    .catch(error => {
        container.innerHTML = "<p style='color:red;'>Error loading data.</p>";
        console.error('Error:', error);
    });
}

function closeModal() {
    document.getElementById('reportModal').style.display = 'none';
}

// Close if they click outside the box
window.onclick = function(event) {
    let modal = document.getElementById('reportModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>