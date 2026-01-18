<div class="branch-card-a"> <!-- Marikina-Pasig Branch -->
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

                $stmt = $conn->prepare($sql);
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

                $sql = "SELECT SUM(principal) AS total_pawn FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_pawn_mp = $row['total_pawn'];
                $pawn_decimal_mp = number_format($total_pawn_mp, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $pawn_decimal_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Redemptions</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(amount) AS total_redeem FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Principal'
                        AND branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_redeem_mp = $row['total_redeem'];
                $redeem_decimal_mp = number_format($total_redeem_mp, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $redeem_decimal_mp </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Interest</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(amount) AS total_int FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Interest'
                        AND branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_int_mp = $row['total_int'];
                $int_decimal_mp = number_format($total_int_mp, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $int_decimal_mp </p>";
            ?>
        </div>    
    </div>
    <div class="debit_credit_cont">
        <div class="val">
            <p>DEBIT</p>
            <?php
                //kasama dito ung sa misc dapat (sa computation lng inde dito sa select statement)
                //Gets the PRINCIPAL of the renewed items this week
                $sql = "SELECT SUM(i.principal) AS total_renew FROM inventory AS i
                        INNER JOIN transactions AS t ON i.item_id = t.item_id
                        WHERE t.created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND t.type_of_pay = 'Interest'
                        AND t.branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_renew_mp = $row['total_renew'];
                
                //AMOUNT (inde na principal) nung transacs this week
                $sql = "SELECT SUM(amount) AS debit_transacs FROM transactions
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND method != 'Cash'
                        AND branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $debit_transacs_mp = $row['debit_transacs'];

                //Ilalagay dito ung select nung sa misc na debit

                //Dito ksama rin ung sa misc (debit is ung expenses etc.)
                $total_debit_mp = $total_pawn_mp + $total_renew_mp + $debit_transacs_mp; //+ $total_debit_misc
                $debit_decimal_mp = number_format($total_debit_mp, 2);

                echo "<p>₱ $debit_decimal_mp</p>";
            ?>
        </div>
        <div class="val">
            <p>CREDIT</p>
            <?php 
                //kasama dito ung sa misc dapat (sa computation lng inde dito sa select statement)
                //Gets the INTEREST of the items pawned this week (Advance Interest)
                $sql = "SELECT SUM(interest) AS adv_int FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1100";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $adv_int_mp = $row['adv_int'];

                //Ilalagay dito ung select nung sa misc na credit

                //Dito ksama rin ung sa misc (credit is ung fund transfers etc.)
                $total_credit_mp = $total_redeem_mp + $total_renew_mp + $adv_int_mp + $total_int_mp; //+ $total_credit_misc
                $credit_decimal_mp = number_format($total_credit_mp, 2);

                echo "<p>₱ $credit_decimal_mp</p>";
            ?>
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
<div class="branch-card-a"> <!-- Quezon City Branch -->
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

                $sql = "SELECT SUM(principal) AS total_pawn FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_pawn_q = $row['total_pawn'];
                $pawn_decimal_q = number_format($total_pawn_q, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $pawn_decimal_q </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Redemptions</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(amount) AS total_redeem FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Principal'
                        AND branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_redeem_q = $row['total_redeem'];
                $redeem_decimal_q = number_format($total_redeem_q, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $redeem_decimal_q </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Interest</div>
        <div class="value">
            <?php
                $role = $_SESSION['role'];

                $sql = "SELECT SUM(amount) AS total_int FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Interest'
                        AND branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_int_q = $row['total_int'];
                $int_decimal_q = number_format($total_int_q, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $int_decimal_q </p>";
            ?>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="val">
            <p>DEBIT</p>
            <?php
                $sql = "SELECT SUM(i.principal) AS total_renew FROM inventory AS i
                        INNER JOIN transactions AS t ON i.item_id = t.item_id
                        WHERE t.created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND t.type_of_pay = 'Interest'
                        AND t.branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_renew_q = $row['total_renew'];
                
                $sql = "SELECT SUM(amount) AS debit_transacs FROM transactions
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND method != 'Cash'
                        AND branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $debit_transacs_q = $row['debit_transacs'];

                $total_debit_q = $total_pawn_q + $total_renew_q + $debit_transacs_q; //+ $total_debit_misc
                $debit_decimal_q = number_format($total_debit_q, 2);

                echo "<p>₱ $debit_decimal_q</p>";
            ?>
        </div>
        <div class="val">
            <p>CREDIT</p>
            <?php 
                $sql = "SELECT SUM(interest) AS adv_int FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1101";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $adv_int_q = $row['adv_int'];

                $total_credit_q = $total_redeem_q + $total_renew_q + $adv_int_q + $total_int_q; //+ $total_credit_misc
                $credit_decimal_q = number_format($total_credit_q, 2);

                echo "<p>₱ $credit_decimal_q</p>";
            ?>
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
<div class="branch-card-a"> <!-- Makati Branch -->
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
                $sql = "SELECT SUM(principal) AS total_pawn FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_pawn_m = $row['total_pawn'];
                $pawn_decimal_m = number_format($total_pawn_m, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $pawn_decimal_m </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Redemptions</div>
        <div class="value">
            <?php
                $sql = "SELECT SUM(amount) AS total_redeem FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Principal'
                        AND branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_redeem_m = $row['total_redeem'];
                $redeem_decimal_m = number_format($total_redeem_m, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $redeem_decimal_m </p>";
            ?>
        </div>

        <div class="info"><span class="circle_purple"></span>Total Interest</div>
        <div class="value">
            <?php
                $sql = "SELECT SUM(amount) AS total_int FROM transactions 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND type_of_pay = 'Interest'
                        AND branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_int_m = $row['total_int'];
                $int_decimal_m = number_format($total_int_m, 2);
                
                echo "<p style=\"background-color: #dcd6e4ff !important; color: #7c5989ff !important;\">₱ $int_decimal_m </p>";
            ?>
        </div>
    </div>
    <div class="debit_credit_cont">
        <div class="val">
            <p>DEBIT</p>
            <?php
                $sql = "SELECT SUM(i.principal) AS total_renew FROM inventory AS i
                        INNER JOIN transactions AS t ON i.item_id = t.item_id
                        WHERE t.created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND t.type_of_pay = 'Interest'
                        AND t.branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $total_renew_m = $row['total_renew'];
                
                $sql = "SELECT SUM(amount) AS debit_transacs FROM transactions
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND method != 'Cash'
                        AND branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $debit_transacs_m = $row['debit_transacs'];

                $total_debit_m = $total_pawn_m + $total_renew_m + $debit_transacs_m; //+ $total_debit_misc
                $debit_decimal_m = number_format($total_debit_m, 2);

                echo "<p>₱ $debit_decimal_m </p>";
            ?>
        </div>
        <div class="val">
            <p>CREDIT</p>
            <?php 
                $sql = "SELECT SUM(interest) AS adv_int FROM inventory 
                        WHERE created_at BETWEEN (CURDATE() - INTERVAL (WEEKDAY(CURDATE())) DAY) 
                        AND (CURDATE() + INTERVAL (6 - WEEKDAY(CURDATE())) DAY)
                        AND branch_id = 1102";

                $stmt = $conn->prepare($sql);
                $stmt->execute();    
                $result = $stmt->get_result();

                $row = $result->fetch_assoc();
                $adv_int_m = $row['adv_int'];

                $total_credit_m = $total_redeem_m + $total_renew_m + $adv_int_m + $total_int_m; //+ $total_credit_misc
                $credit_decimal_m = number_format($total_credit_m, 2);

                echo "<p>₱ $credit_decimal_m </p>";
            ?>
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