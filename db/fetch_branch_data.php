<?php
include '../config/db_conn.php';

if(isset($_POST['branch_id'])) {
    $branch_id = $_POST['branch_id'];

    $sql1 = "SELECT SUM(principal) as total FROM inventory WHERE branch_id = ? AND status != 'Redeemed'";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("i", $branch_id);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $principal = number_format($res1['total'] ?? 0, 2);

    $sql2 = "SELECT COUNT(*) as t_count FROM transactions WHERE branch_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $branch_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result()->fetch_assoc();
    $t_count = $res2['t_count'] ?? 0;

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
    echo "
    <div class='top_analytics'>
        <div class='top_analytics_title'>
            <p>TOTAL PRINCIPAL OUTSTANDING</p>
        </div>
        <div class='top_analytics_value'>
            <p>₱ $principal </p>
            <p class=\"$trend_class\"> $trend_output_principal </p>
        </div>
    </div>
    
   ";
}
?>