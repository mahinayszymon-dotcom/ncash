<?php
include '../config/db_conn.php';

session_start(); 

if(isset($_POST['lq_role'])) {
    $lq_role = $_POST['lq_role'];
    $lq_branch = $_POST['lq_branch'];

    if($lq_role == "admin")
    {
        switch($lq_branch)
        {
            case 'pasig':
                $whereSql = " WHERE branch_id = 1100";
                break;
            case 'quezon':
                $whereSql = " WHERE branch_id = 1101";
                break;
            case 'makati':
                $whereSql = " WHERE branch_id = 1102";
                break;
            case 'all':
                $whereSql = "";
                break;
        }
    }
    else 
    {
        switch($lq_branch)
        {
            case 'pasig':
                $whereSql = " WHERE branch_id = 1100";
                break;
            case 'quezon':
                $whereSql = " WHERE branch_id = 1101";
                break;
            case 'makati':
                $whereSql = " WHERE branch_id = 1102";
                break;
        }
    }

    $sql1 = "SELECT SUM(principal) as total FROM items_liquidated" . $whereSql;
    $stmt1 = $conn->prepare($sql1);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $principal = number_format($res1['total'] ?? 0, 2);
        
    // output (ikaw bahala if ppaltan pa yung "TOTAL TURNOVER PRINCIPAL")
    echo "
    <div class='top_analytics'>
        <div class='top_analytics_title'>
            <p>TOTAL TURNOVER PRINCIPAL</p>
        </div>
        <div class='top_analytics_value'>
            <p>₱ $principal </p>
        </div>
    </div>
    
   ";

    $branch_to_process = [];

    if ($lq_role == "admin") 
    {
        if ($lq_branch == 'all') 
        {
            // Define the 3 iterations for 'all'
            $branch_to_process = [
                'Pasig' => 1100,
                'Quezon' => 1101,
                'Makati' => 1102
            ];
        } 
        else 
        {
            // Define only the single selected branch
            $branch_ids = ['pasig' => 1100, 'quezon' => 1101, 'makati' => 1102];
            $branch_to_process = [ucfirst($lq_branch) => $branch_ids[$lq_branch]];
        }
    }
    else 
    {
        $user_b_id = $_SESSION['branch_id']; 
        $branch_map = [
            1100 => 'Pasig',
            1101 => 'Quezon',
            1102 => 'Makati'
        ];

        $display_name = isset($branch_map[$user_b_id]) ? $branch_map[$user_b_id] : 'Unknown Branch';

        $branch_to_process = [$display_name => $user_b_id];
    }
    

    // 2. Loop through the branches array
    foreach ($branch_to_process as $branch_name => $branch_id) {
        
        // Update the WHERE clause for this specific iteration
        $whereSql = " WHERE il.branch_id = $branch_id"; 

        $sql2 = "SELECT il.liquidated_at, il.agreement_num, COALESCE(c.fullname, ca.fullname) AS fullname, il.item_name, il.remarks, il.principal
                    FROM items_liquidated AS il
                    LEFT JOIN clients AS c ON il.client_id = c.client_id
                    LEFT JOIN clients_archive AS ca ON il.client_id = ca.client_id"
                    . $whereSql
                    . " ORDER BY il.liquidated_at ASC";

        $stmt2 = $conn->prepare($sql2);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        $res2_number = 1;

        // Display Branch Header
        echo "<br><h1 style='font-size: 16px; color: var(--purple);'>Branch: $branch_name</h1>";
        echo "<h2 style='font-size: 14px;'>Liquidations</h2>
            <h4 style='font-size: 10px;'>Item List</h4>";

        echo "<table border='1' style='width:100%; border-collapse: collapse;'>
                <thead>
                    <tr>
                        <th style='font-size: 10px;'>#</th>
                        <th style='font-size: 10px;'>AN</th>
                        <th style='font-size: 10px;'>Client Name</th>
                        <th style='font-size: 10px;'>Item Name</th>
                        <th style='font-size: 10px;'>Item Description</th>
                        <th style='font-size: 10px;'>Principal</th>
                        <th style='font-size: 10px;'>Liquidation Date</th>
                    </tr>
                </thead>
                <tbody>";

        if ($res2->num_rows > 0) {
            $res2_ttl_pawn = 0.00;
            while ($res2_row = $res2->fetch_assoc()) {
                $lq_date = htmlspecialchars($res2_row['liquidated_at']);
                $lq_agrmnt = htmlspecialchars($res2_row['agreement_num']);
                $lq_c_name = htmlspecialchars($res2_row['fullname']);
                $lq_i_name = htmlspecialchars($res2_row['item_name']);
                $lq_remarks = htmlspecialchars($res2_row['remarks']);
                $lq_principal = (float)$res2_row['principal'];
                
                $lq_p_deci = number_format($lq_principal, 2);
                $lq_format_date = date("M d, Y", strtotime($lq_date));

                echo "<tr>
                        <td style='font-size: 10px;'>$res2_number</td>
                        <td style='font-size: 10px;'>$lq_agrmnt</td>
                        <td style='font-size: 10px;'>$lq_c_name</td>
                        <td style='font-size: 10px;'>$lq_i_name</td>
                        <td style='font-size: 10px;'>$lq_remarks</td>
                        <td style='font-size: 10px;'>₱ $lq_p_deci</td>
                        <td style='font-size: 10px;'>$lq_format_date</td>
                    </tr>";

                $res2_number++;
                $res2_ttl_pawn += $lq_principal;
            }

            $res2_ttl_display = number_format($res2_ttl_pawn, 2);
            echo "<tr>
                    <td colspan='5' style='font-size: 10px; text-align:right;'><b>Total:</b></td>
                    <td colspan='2' style='font-size: 10px;'><b>₱ $res2_ttl_display</b></td>
                </tr>";
        } else {
            echo "<tr><td colspan='7' class='no_records_found' style='font-size: 10px;'>No liquidations for $branch_name</td></tr>";
        }
        echo "</tbody></table><hr>";
    }

    if (!empty($branch_to_process)) {
        //Collect all ids looped through
        $ids_to_del = array_values($branch_to_process);
        
        //create a string to be used in the delete function
        $placeholders = implode(',', array_fill(0, count($ids_to_del), '?'));
        
        //... is splat operator that unpacks the ids from the array (removes '[]')
        $del_sql = "DELETE FROM items_liquidated WHERE branch_id IN ($placeholders)";
        $del_stmt = $conn->prepare($del_sql);
        $del_stmt->bind_param(str_repeat('i', count($ids_to_del)), ...$ids_to_del);
        $del_stmt->execute();
    }
}