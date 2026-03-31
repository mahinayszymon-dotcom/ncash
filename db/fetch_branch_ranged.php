<?php
include '../config/db_conn.php';

if(isset($_POST['branch_id'])) {
    $branch_id = $_POST['branch_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql1 = "SELECT SUM(principal) as total FROM inventory WHERE branch_id = ? AND status != 'Redeemed' AND DATE(created_at) BETWEEN ? AND ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("iss", $branch_id, $start_date, $end_date);
    $stmt1->execute();
    $res1 = $stmt1->get_result()->fetch_assoc();
    $principal = number_format($res1['total'] ?? 0, 2);

    $sql2 = "SELECT COUNT(*) as t_count FROM transactions WHERE branch_id = ? AND DATE(created_at) BETWEEN ? AND ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("iss", $branch_id, $start_date, $end_date);
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
        </div>
    </div>
    
   ";

   //Debit records
   //All Pawns (sanla)
   $sql3 = "SELECT agreement_num, principal, created_at FROM inventory 
            WHERE DATE(created_at) BETWEEN ? AND ? 
            AND branch_id = ? 
            AND is_omitted != 1
            ORDER BY created_at ASC";
    $stmt3 = $conn->prepare($sql3);
    $stmt3->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt3->execute();
    $res3 = $stmt3->get_result();
    $res3_number = 1;

    echo "<br>
            <h2 style='font-size: 1rem;'>Debits</h2>
            <h4 style='font-size: 0.90rem;'>Total Pawns (Sanla)</h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>AN</th>
                    <th style='font-size: 0.90rem;'>Principal</th>
                    <th style='font-size: 0.90rem;'>Date of Pawn</th>
                </tr>
            </thead>
            <tbody>";
            if($res3->num_rows > 0)
            {
                $res3_ttl_pawn = 0.00;
                while($res3_row = $res3->fetch_assoc())
                {
                    $res3_agreement_num = htmlspecialchars($res3_row['agreement_num']);
                    $res3_principal = htmlspecialchars($res3_row['principal']);
                    $res3_c_date = htmlspecialchars($res3_row['created_at']);

                    $res3_p_deci = number_format((float) $res3_principal, 2);
                    $res3_format_date = date("M d, Y", strtotime($res3_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res3_number</td>
                        <td style='font-size: 0.90rem;'>$res3_agreement_num</td>
                        <td style='font-size: 0.90rem;'>₱ $res3_p_deci</td>
                        <td style='font-size: 0.90rem;'>$res3_format_date</td>
                    </tr>
                    ";

                    $res3_number++;
                    $res3_ttl_pawn += (float)$res3_principal;
                }

                $res3_ttl_deci = number_format((float) $res3_ttl_pawn, 2);
                echo 
                "
                <tr>
                    <td colspan='4' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res3_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='4' class='no_records_found' style='font-size: 0.90rem;'> No pawn(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //Transactions that are not cash (Renew)
    $sql4 = "SELECT t.agreement_num, t.method, t.amount, i.principal, t.created_at 
            FROM transactions AS t 
            INNER JOIN inventory AS i ON t.item_id = i.item_id
            WHERE DATE(t.created_at) BETWEEN ? AND ? 
            AND t.type_of_pay = 'Interest'
            AND t.branch_id = ? 
            AND t.method != 'Cash'
            ORDER BY t.created_at ASC";
    $stmt4 = $conn->prepare($sql4);
    $stmt4->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt4->execute();
    $res4 = $stmt4->get_result();
    $res4_number = 1;

    echo "
            <br>
            <h4 style='font-size: 0.90rem;'>Total Online / Bank Renewals </h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>AN</th>
                    <th style='font-size: 0.90rem;'>Method</th>
                    <th style='font-size: 0.90rem;'>Principal</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res4->num_rows > 0)
            {
                $res4_debit_renew = 0.00;
                while($res4_row = $res4->fetch_assoc())
                {
                    $res4_agreement_num = htmlspecialchars($res4_row['agreement_num']);
                    $res4_method = htmlspecialchars($res4_row['method']);
                    $res4_principal = htmlspecialchars($res4_row['principal']);
                    $res4_amount = htmlspecialchars($res4_row['amount']);
                    $res4_c_date = htmlspecialchars($res4_row['created_at']);

                    $res4_p_deci = number_format((float) $res4_principal, 2);
                    $res4_amt_deci = number_format((float) $res4_amount, 2);
                    $res4_format_date = date("M d, Y", strtotime($res4_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res4_number</td>
                        <td style='font-size: 0.90rem;'>$res4_agreement_num</td>
                        <td style='font-size: 0.90rem;'>$res4_method</td>
                        <td style='font-size: 0.90rem;'>₱ $res4_p_deci</td>
                        <td style='font-size: 0.90rem;'>₱ $res4_amt_deci</td>
                        <td style='font-size: 0.90rem;'>$res4_format_date</td>
                    </tr>
                    ";

                    $res4_number++;
                    $res4_debit_renew += (float)$res4_principal;
                    $res4_debit_renew += (float)$res4_amount;
                }

                $res4_ttl_deci = number_format((float) $res4_debit_renew, 2);
                echo 
                "
                <tr>
                    <td colspan='6' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res4_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='6' class='no_records_found' style='font-size: 0.90rem;'> No renewal(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //Transactions that are not cash (Redeem)
    $sql5 = "SELECT t.agreement_num, t.method, t.amount, i.principal, t.created_at 
            FROM transactions AS t 
            INNER JOIN inventory AS i ON t.item_id = i.item_id
            WHERE DATE(t.created_at) BETWEEN ? AND ? 
            AND t.type_of_pay = 'Principal'
            AND t.branch_id = ? 
            AND t.method != 'Cash'
            ORDER BY t.created_at ASC";
    $stmt5 = $conn->prepare($sql5);
    $stmt5->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt5->execute();
    $res5 = $stmt5->get_result();
    $res5_number = 1;

    echo "
            <br>
            <h4 style='font-size: 0.90rem;'>Total Online / Bank Redemptions (Tubos)</h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>AN</th>
                    <th style='font-size: 0.90rem;'>Method</th>
                    <th style='font-size: 0.90rem;'>Principal</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res5->num_rows > 0)
            {
                $res5_debit_redeem = 0.00;
                while($res5_row = $res5->fetch_assoc())
                {
                    $res5_agreement_num = htmlspecialchars($res5_row['agreement_num']);
                    $res5_method = htmlspecialchars($res5_row['method']);
                    $res5_principal = htmlspecialchars($res5_row['principal']);
                    $res5_amount = htmlspecialchars($res5_row['amount']);
                    $res5_c_date = htmlspecialchars($res5_row['created_at']);

                    $res5_p_deci = number_format((float) $res5_principal, 2);
                    $res5_amt_deci = number_format((float) $res5_amount, 2);
                    $res5_format_date = date("M d, Y", strtotime($res5_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res5_number</td>
                        <td style='font-size: 0.90rem;'>$res5_agreement_num</td>
                        <td style='font-size: 0.90rem;'>$res5_method</td>
                        <td style='font-size: 0.90rem;'>₱ $res5_p_deci</td>
                        <td style='font-size: 0.90rem;'>₱ $res5_amt_deci</td>
                        <td style='font-size: 0.90rem;'>$res5_format_date</td>
                    </tr>
                    ";

                    $res5_number++;
                    $res5_debit_redeem += (float)$res5_principal;
                    $res5_debit_redeem += (float)$res5_amount;
                }

                $res5_ttl_deci = number_format((float) $res5_debit_redeem, 2);
                echo 
                "
                <tr>
                    <td colspan='6' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res5_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='6' class='no_records_found' style='font-size: 0.90rem;'> No redemption(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //End-bal debit transactions
    $sql6 = "SELECT label, amount, created_at 
            FROM eb_transactions 
            WHERE DATE(created_at) BETWEEN ? AND ? 
            AND type_of_transac = 'Debit'
            AND branch_id = ? 
            ORDER BY created_at ASC";
    $stmt6 = $conn->prepare($sql6);
    $stmt6->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt6->execute();
    $res6 = $stmt6->get_result();
    $res6_number = 1;

    echo "
            <br>
            <h4 style='font-size: 0.90rem;'>Miscellaneous Transactions (Debit)</h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>Label</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res6->num_rows > 0)
            {
                $res6_debit_eb = 0.00;
                while($res6_row = $res6->fetch_assoc())
                {
                    $res6_t_lbl = htmlspecialchars($res6_row['label']);
                    $res6_amount = htmlspecialchars($res6_row['amount']);
                    $res6_c_date = htmlspecialchars($res6_row['created_at']);

                    $res6_amt_deci = number_format((float) $res6_amount, 2);
                    $res6_format_date = date("M d, Y", strtotime($res6_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res6_number</td>
                        <td style='font-size: 0.90rem;'>$res6_t_lbl</td>
                        <td style='font-size: 0.90rem;'>₱ $res6_amt_deci</td>
                        <td style='font-size: 0.90rem;'>$res6_format_date</td>
                    </tr>
                    ";

                    $res6_number++;
                    $res6_debit_eb += (float)$res6_principal;
                    $res6_debit_eb += (float)$res6_amount;
                }

                $res6_ttl_deci = number_format((float) $res6_debit_eb, 2);
                echo 
                "
                <tr>
                    <td colspan='4' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res6_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='4' class='no_records_found' style='font-size: 0.90rem;'> No miscellaneous transaction(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //Credit Records
    //All cash renewals
    echo "
            <h2 style='font-size: 1rem;'>Credits</h2>
            <h4 style='font-size: 0.90rem;'>Total Cash Renewals </h4>";

    $sql7 = "SELECT t.agreement_num, t.method, t.amount, i.principal, t.created_at 
            FROM transactions AS t 
            INNER JOIN inventory AS i ON t.item_id = i.item_id
            WHERE DATE(t.created_at) BETWEEN ? AND ? 
            AND t.type_of_pay = 'Interest'
            AND t.branch_id = ? 
            AND t.method = 'Cash'
            ORDER BY t.created_at ASC";
    $stmt7 = $conn->prepare($sql7);
    $stmt7->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt7->execute();
    $res7 = $stmt7->get_result();
    $res7_number = 1;

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>AN</th>
                    <th style='font-size: 0.90rem;'>Method</th>
                    <th style='font-size: 0.90rem;'>Principal</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res7->num_rows > 0)
            {
                $res7_credit_renew = 0.00;
                while($res7_row = $res7->fetch_assoc())
                {
                    $res7_agreement_num = htmlspecialchars($res7_row['agreement_num']);
                    $res7_method = htmlspecialchars($res7_row['method']);
                    $res7_principal = htmlspecialchars($res7_row['principal']);
                    $res7_amount = htmlspecialchars($res7_row['amount']);
                    $res7_c_date = htmlspecialchars($res7_row['created_at']);

                    $res7_p_deci = number_format((float) $res7_principal, 2);
                    $res7_amt_deci = number_format((float) $res7_amount, 2);
                    $res7_format_date = date("M d, Y", strtotime($res7_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res7_number</td>
                        <td style='font-size: 0.90rem;'>$res7_agreement_num</td>
                        <td style='font-size: 0.90rem;'>$res7_method</td>
                        <td style='font-size: 0.90rem;'>₱ $res7_p_deci</td>
                        <td style='font-size: 0.90rem;'>₱ $res7_amt_deci</td>
                        <td style='font-size: 0.90rem;'>$res7_format_date</td>
                    </tr>
                    ";

                    $res7_number++;
                    $res7_credit_renew += (float)$res7_principal;
                    $res7_credit_renew += (float)$res7_amount;
                }

                $res7_ttl_deci = number_format((float) $res7_credit_renew, 2);
                echo 
                "
                <tr>
                    <td colspan='6' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res7_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='6' class='no_records_found' style='font-size: 0.90rem;'> No renewal(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //All cash redeems
    $sql8 = "SELECT t.agreement_num, t.method, t.amount, i.principal, t.created_at 
            FROM transactions AS t 
            INNER JOIN inventory AS i ON t.item_id = i.item_id
            WHERE DATE(t.created_at) BETWEEN ? AND ? 
            AND t.type_of_pay = 'Principal'
            AND t.branch_id = ? 
            AND t.method = 'Cash'
            ORDER BY t.created_at ASC";
    $stmt8 = $conn->prepare($sql8);
    $stmt8->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt8->execute();
    $res8 = $stmt8->get_result();
    $res8_number = 1;

    echo "
            <br>
            <h4 style='font-size: 0.90rem;'>Total Cash Redemptions (Tubos)</h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>AN</th>
                    <th style='font-size: 0.90rem;'>Method</th>
                    <th style='font-size: 0.90rem;'>Principal</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res8->num_rows > 0)
            {
                $res8_credit_redeem = 0.00;
                while($res8_row = $res8->fetch_assoc())
                {
                    $res8_agreement_num = htmlspecialchars($res8_row['agreement_num']);
                    $res8_method = htmlspecialchars($res8_row['method']);
                    $res8_principal = htmlspecialchars($res8_row['principal']);
                    $res8_amount = htmlspecialchars($res8_row['amount']);
                    $res8_c_date = htmlspecialchars($res8_row['created_at']);

                    $res8_p_deci = number_format((float) $res8_principal, 2);
                    $res8_amt_deci = number_format((float) $res8_amount, 2);
                    $res8_format_date = date("M d, Y", strtotime($res8_c_date));

                    echo 
                    "
                    <tr>
                        <td>$res8_number</td>
                        <td>$res8_agreement_num</td>
                        <td>$res8_method</td>
                        <td>₱ $res8_p_deci</td>
                        <td>₱ $res8_amt_deci</td>
                        <td>$res8_format_date</td>
                    </tr>
                    ";

                    $res8_number++;
                    $res8_credit_redeem += (float)$res8_principal;
                    $res8_credit_redeem += (float)$res8_amount;
                }

                $res8_ttl_deci = number_format((float) $res8_credit_redeem, 2);
                echo 
                "
                <tr>
                    <td colspan='6' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res8_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='6' class='no_records_found' style='font-size: 0.90rem;'> No redemption(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    //End-bal credit transactions
    $sql9 = "SELECT label, amount, created_at 
            FROM eb_transactions 
            WHERE DATE(created_at) BETWEEN ? AND ? 
            AND type_of_transac = 'Credit'
            AND branch_id = ? 
            ORDER BY created_at ASC";
    $stmt9 = $conn->prepare($sql9);
    $stmt9->bind_param("ssi", $start_date, $end_date, $branch_id);
    $stmt9->execute();
    $res9 = $stmt9->get_result();
    $res9_number = 1;

    echo "
            <br>
            <h4 style='font-size: 0.90rem;'>Miscellaneous Transactions (Credit)</h4>";

    echo "<table>
            <thead>
                <tr>
                    <th style='font-size: 0.90rem;'>#</th>
                    <th style='font-size: 0.90rem;'>Label</th>
                    <th style='font-size: 0.90rem;'>Amount</th>
                    <th style='font-size: 0.90rem;'>Transaction Date</th>
                </tr>
            </thead>
            <tbody>";
            if($res9->num_rows > 0)
            {
                $res9_debit_eb = 0.00;
                while($res9_row = $res9->fetch_assoc())
                {
                    $res9_t_lbl = htmlspecialchars($res9_row['label']);
                    $res9_amount = htmlspecialchars($res9_row['amount']);
                    $res9_c_date = htmlspecialchars($res9_row['created_at']);

                    $res9_amt_deci = number_format((float) $res9_amount, 2);
                    $res9_format_date = date("M d, Y", strtotime($res9_c_date));

                    echo 
                    "
                    <tr>
                        <td style='font-size: 0.90rem;'>$res9_number</td>
                        <td style='font-size: 0.90rem;'>$res9_t_lbl</td>
                        <td style='font-size: 0.90rem;'>₱ $res9_amt_deci</td>
                        <td style='font-size: 0.90rem;'>$res9_format_date</td>
                    </tr>
                    ";

                    $res9_number++;
                    $res9_debit_eb += (float)$res9_principal;
                    $res9_debit_eb += (float)$res9_amount;
                }

                $res9_ttl_deci = number_format((float) $res9_debit_eb, 2);
                echo 
                "
                <tr>
                    <td colspan='4' style='font-size: 0.90rem;'><b>Total:</b> ₱ $res9_ttl_deci</td>
                </tr>
                ";
            }
            else
            {
                echo
                "
                    <tr>
                        <td colspan='4' class='no_records_found' style='font-size: 0.90rem;'> No miscellaneous transaction(s) this period</td>
                    </tr>
                ";
            }
    echo "</tbody>
        </table>";

    if($branch_id == "1100")
    {
        $sql_column = "mp_branch";
    }
    else if($branch_id == "1101")
    {
        $sql_column = "q_branch";
    }
    else if($branch_id == "1102")
    {
        $sql_column = "m_branch";
    }

    //End Balance amount
    $bal_sql = "SELECT $sql_column FROM balance_history WHERE DATE(date) <= ?
                ORDER BY date DESC 
                LIMIT 1";
    $bal_stmt = $conn->prepare($bal_sql);
    $bal_stmt->bind_param("s", $end_date);
    $bal_stmt->execute();
    $bal_res = $bal_stmt->get_result();
    
    if($bal_res->num_rows > 0)
    {
        $bal_row = $bal_res->fetch_assoc();
        $br_bal = htmlspecialchars($bal_row[$sql_column]);
        $br_bal_deci = number_format((float) $br_bal, 2);
    }
    else 
    {
        $br_bal_deci = "0.00";
    }

    echo "
            <br>
            <h2 style='font-size: 1rem;'>End Balance: ₱ $br_bal_deci</h2>";
}
?>