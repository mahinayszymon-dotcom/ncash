<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
include("../../db/branch_fetch.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item</title>
    <link rel="icon" type="image/png" href="../../resources/img/favicon.png">
    <link rel="stylesheet" href="../../resources/css/base.css">
    <link rel="stylesheet" href="../../resources/css/colors.css">
    <link rel="stylesheet" href="../../resources/css/fonts.css">
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/transactions.css">
    <style>
        .transaction_type a:nth-child(2) {
            background-color: var(--late) !important;
            color: var(--main-content) !important;
            opacity: 1;
        }

        .transaction_type a:nth-child(2):hover {
            border: none;
            border-radius: 5px;
            /* background-color: var(--red-dark) !important; */
            color: var(--main-content) !important;
            opacity: 1;
        }
    </style>
</head>
<body>
    <main class="dashboard">
        <section class="navigation_bar">
            <?php
                include('../../includes/nav_bar.php')
            ?>
        </section>
        <section class="main_content">
            <div class="top_panel">
                <div class="top_panel_content">
                    <div class="text_cont">
                        <h1>Add Transaction</h1>
                    </div>
                    <div class="search_cont">
                        <input type="text" placeholder="<?php echo "What would you like to search this " . date('l') . "?";?>">
                        <img src="../../resources/img/icons/search.png" alt="search">
                    </div>
                    <div class="account_cont">       
                        <div class="profile_circle">
                            <?php
                                echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                            ?>
                        </div>
                        <div class="profile_text">
                            <?php
                                echo "<a href=\"../../dashboard/settings.php\"><p>" . ucwords($fullname) . "</p></a>";
                                echo "<p>" . ucwords($branch_name) . " (" . ucwords($role) . ")</p>";
                            ?>
                        </div>
                        <div class="account_cont_actions"> 
                            <button onclick="window.location.href='../../dashboard/notifications.php';"><img src="../../resources/img/icons/notif.png" alt="notifications"></button>   
                            <form id="logout-form" action="../../auth/logout.php" method="POST">
                                <button type="submit"><img src="../../resources/img/icons/logout.png" alt="logout"></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="central_panelC">
                <div class="data_controlsB">
                    <div class="data_controls_header">
                        <div class="data_controls_header_text">
                            <div class="icon_normal">
                                <img src="../../resources/img/icons/add_w.png" alt="add">
                            </div>
                            <h2>Add New Transaction</h2>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../transactions.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return to Transactions</button>
                        </div>
                    </div>
                    <hr>
                    <div class="data_controls_form">
                        <div class="form">   
                            <?php
                                if(isset($_POST['submit'])) 
                                {
                                    date_default_timezone_set('Asia/Manila');

                                    $selected_id = htmlspecialchars($_POST['agreement_num']);
                                    $type_of_pay = htmlspecialchars($_POST['type_of_payment']);
                                    $s_count = 0;
                                    $curDate = new DateTime();

                                    $pay_parts = 
                                    [
                                        [
                                            'amount' => trim(htmlspecialchars($_POST['amount_one'])),
                                            'method' => htmlspecialchars($_POST['mode_of_payment_one'])
                                        ],
                                        [
                                            'amount' => trim(htmlspecialchars($_POST['amount_two'])),
                                            'method' => htmlspecialchars($_POST['mode_of_payment_two'])
                                        ]
                                    ];

                                    if ($pay_parts[0]['amount'] <= 0 || $pay_parts[1]['amount'] <= 0) 
                                    {
                                        $_SESSION['error_msg'] = "Both amounts must be greater than zero.";
                                    } 
                                    else if ($pay_parts[0]['method'] === $pay_parts[1]['method']) 
                                    {
                                        $_SESSION['error_msg'] = "Methods of payment must differ for split transactions.";
                                    } 
                                    else 
                                    {
                                        $sql = "SELECT due_date, branch_id, client_id, agreement_num FROM inventory WHERE item_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $selected_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if($result->num_rows > 0) 
                                        {
                                            $row = $result->fetch_assoc();
                                            $date_str = htmlspecialchars($row['due_date']);
                                            $dueDate = new DateTime($date_str);
                                            
                                            $item_branch = $row['branch_id'];
                                            $client_id = $row['client_id'];
                                            $agreement_num = $row['agreement_num'];

                                            $u_success = false;

                                            if($type_of_pay == "Principal") 
                                            {
                                                $p_val = htmlspecialchars($_POST['principal']);
                                                $p_expect = trim($p_val, '₱ ');
                                                $ttl_paid = $pay_parts[0]['amount'] + $pay_parts[1]['amount'];

                                                if($ttl_paid == $p_expect) 
                                                {
                                                    $current = $curDate->format('Y-m-d H:i:s');
                                                    $sql = "UPDATE inventory SET status = 'Redeemed', updated_at = ? WHERE item_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("si", $current, $selected_id);
                                                    if($stmt->execute()) 
                                                    { 
                                                        $u_success = true; 
                                                    }
                                                } 
                                                else 
                                                {
                                                    $_SESSION['error_msg'] = "Total of both amounts should be equal to the Principal.";
                                                }
                                            } 
                                            else if($type_of_pay == "Interest")
                                            {
                                                $i_val = htmlspecialchars($_POST['interest']);
                                                $i_expect = trim($i_val, '₱ ');
                                                $ttl_paid = $pay_parts[0]['amount'] + $pay_parts[1]['amount'];

                                                if($ttl_paid == $i_expect) 
                                                {
                                                    if($dueDate >= $curDate)
                                                    {
                                                        $dueDate->modify("+30 days");
                                                        $newDate = $dueDate->format('Y-m-d H:i:s');
                                                    }
                                                    else
                                                    {
                                                        $curDate->modify("+30 days");
                                                        $newDate = $curDate->format('Y-m-d H:i:s');
                                                    }

                                                    $sql = "UPDATE inventory SET due_date = ?, status = 'Active' WHERE item_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("si", $newDate, $selected_id);
                                                    if($stmt->execute()) 
                                                    { 
                                                        $u_success = true; 
                                                    }
                                                } 
                                                else 
                                                {
                                                    $_SESSION['error_msg'] = "Total of both amounts should be equal to the Interest.";
                                                }
                                            }

                                            if($u_success) 
                                            {
                                                $creator = $_SESSION['username'];
                                                $is_linked = 1; 

                                                foreach ($pay_parts as $part) 
                                                {
                                                    $part_amount = $part['amount'];
                                                    $part_method = $part['method'];

                                                    $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $item_branch);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();

                                                    $fetch_eb = (float)htmlspecialchars($row['end_balance']);
                                                    $upd_success = false;

                                                    $sql = "UPDATE branches
                                                            SET end_balance = ?
                                                            WHERE branch_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    if(isset($fetch_eb))
                                                    {
                                                        if($part_method == "Cash")
                                                        {
                                                            $new_eb_val = $fetch_eb + (float)$part_amount; 
                                                            $stmt->bind_param("di", $new_eb_val, $branch_id);
                                                            if($stmt->execute())
                                                            {
                                                                $upd_success = true;
                                                            }
                                                        }
                                                        else 
                                                        {
                                                            $new_eb_val = ($fetch_eb + (float)$part_amount) - (float)$part_amount; //no change (cancel out)
                                                            $stmt->bind_param("di", $new_eb_val, $branch_id);
                                                            if($stmt->execute())
                                                            {
                                                                $upd_success = true;
                                                            }
                                                        }
                                                    }
                                                    else 
                                                    {
                                                        $upd_success = true;
                                                    }

                                                    if($upd_success)
                                                    {
                                                        $sql = "INSERT INTO transactions (agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, method, paid_date, is_linked)
                                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("iiiidssssi", $agreement_num, $client_id, $item_branch, $selected_id, $part_amount, $type_of_pay, $creator, $part_method, $date_str, $is_linked);
                                                        
                                                        if($stmt->execute()) 
                                                        {
                                                            $s_count++;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if($s_count == 2) 
                                    {
                                        $audit_u_id = $_SESSION['user_id'];
                                        $audit_action = "Created";
                                        $audit_obj = "Transaction";
                                        $audit_desc = "Created split $type_of_pay transaction for agreement no. $agreement_num";

                                        $curDate = new DateTime();
                                        $current = $curDate->format('Y-m-d H:i:s');

                                        $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                VALUES (?, ?, ?, ?, ?, ?)";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $item_branch, $current);
                                        if($stmt->execute())
                                        {
                                            $_SESSION['transac_success_msg'] = "Split Transaction recorded successfully!";
                                        }
                                    }
                                }
                            ?>     
                            <form action="add_dual.php" method="POST">
                                <div class="fullwidth">
                                    <div class="result_cont">
                                        <?php
                                            if (isset($_SESSION['transac_success_msg'])) {
                                                $redirect_url = "../../dashboard/transactions.php";
                                                // echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['success_msg'] . "</span>";
                                                $delay = 0.5; // three seconds muna taymperst
                                                
                                                // iredirect papuntang inventory page
                                                echo "<meta http-equiv='refresh' content='" . $delay . "; url=" . $redirect_url . "'>";
                                            } else if (isset($_SESSION['error_msg'])) {
                                                echo "<span class=\"message_error\"><img src=\"../../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['error_msg'] . "</span>";
                                                unset($_SESSION['error_msg']);
                                            } else {
                                                echo "<span class=\"message_info\"><img src=\"../../resources/img/icons/info.png\" alt=\"info\">" . "Quick Tip: Do not leave any input fields incomplete!" . "</span>";
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="fullwitdh">
                                    <div class="transaction_type">
                                        <a href="add.php">Single</a>
                                        <a href="add_dual.php">Dual</a>
                                    </div>
                                </div>
                                <div class="fullwidth">
                                    <br>
                                    <p>Select Agreement Number</p>
                                </div>
                                <div class="fullwidth">
                                    <label for="agreement_num">Agreement Number<i style="color:red;">*</i></label>
                                    <select name="agreement_num" id="agreement_num" required onchange="fetchLoan()">
                                        <option value="" disabled selected>--Select--</option>
                                        <?php 
                                            $sql = "SELECT i.item_id, i.agreement_num, 
                                                    CASE
                                                        WHEN b.branch_name = 'Marikina-Pasig' THEN 'MP'
                                                        WHEN b.branch_name = 'Makati' THEN 'M'
                                                        ELSE LEFT(b.branch_name, 1)
                                                    END AS branch_letter
                                                    FROM inventory as i
                                                    INNER JOIN branches as b ON i.branch_id = b.branch_id
                                                    WHERE status != 'Redeemed'";
                                                
                                            if($role != 'admin')
                                            {
                                                $sql .= " AND i.branch_id = ?";
                                            }

                                            $sql .= " ORDER BY agreement_num DESC";

                                            $stmt = $conn->prepare($sql);
                            
                                            if($role != 'admin')
                                            {
                                                $stmt->bind_param("i", $branch_id);
                                            }
                            
                                            $stmt->execute();
                                                
                                            $result = $stmt->get_result();
                                            if($result->num_rows > 0)
                                            {
                                                while($row = $result->fetch_assoc())
                                                {
                                                    $agreement_num = htmlspecialchars($row['agreement_num']);
                                                    $branch_letter = htmlspecialchars($row['branch_letter']);
                                                    $item_id = htmlspecialchars($row['item_id']);
                                                    echo "<option value='$item_id'> $branch_letter#$agreement_num </option>";
                                                }
                                            }
                                        ?>
                                    </select>
                                </div>
                                <div class="fullwidth">
                                    <br>
                                    <p>Item Information</p>
                                </div>
                                <div class="input_cont">
                                    <label for="client_name">Client Name</label>
                                    <input type="text" name="client_name" id="client_name" value=" " readonly>
                                </div>
                                <div class="input_cont">
                                    <label for="item_name">Item Name</label>
                                    <input type="text" name="item_name" id="item_name" value=" " readonly>
                                </div>
                                <div class="input_cont">
                                    <label for="interest">Interest</label>
                                    <input type="text" name="interest" id="interest" value="₱ " readonly>
                                </div>
                                <div class="input_cont">
                                    <label for="principal">Principal</label>
                                    <input type="text" name="principal" id="principal" value="₱ " readonly>
                                </div>
                                <div class="fullwidth">
                                    <br>
                                    <p>Transaction Details</p>
                                </div>
                                <div class="input_cont">
                                    <label for="mode_of_payment_one">Method of Payment #1<i style="color:red;">*</i></label>
                                    <select name="mode_of_payment_one" id="mode_of_payment_one" required>
                                        <option value="" disabled selected>--Select Method--</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online</option>
                                        <option value="Bank">Bank</option>
                                    </select>
                                </div>
                                <div class="input_cont">
                                    <label for="amount_one">Amount<i style="color:red;">*</i></label>
                                    <input type="text" name="amount_one" id="amount_one"  pattern="[0-9]*" required></input>
                                </div>
                                <div class="fullwidth"><br></div>
                                <div class="input_cont">
                                    <label for="mode_of_payment_two">Method of Payment #2<i style="color:red;">*</i></label>
                                    <select name="mode_of_payment_two" id="mode_of_payment_two" required>
                                        <option value="" disabled selected>--Select Method--</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online</option>
                                        <option value="Bank">Bank</option>
                                    </select>
                                </div>
                                <div class="input_cont">
                                    <label for="amount_two">Amount<i style="color:red;">*</i></label>
                                    <input type="text" name="amount_two" id="amount_two"  pattern="[0-9]*" required></input>
                                </div>
                                <div class="fullwidth"><br></div>
                                <div class="input_cont">
                                    <label for="type_of_payment_one">Type of Payment<i style="color:red;">*</i></label>
                                    <select name="type_of_payment" id="type_of_payment" required>
                                        <option value="" disabled selected>--Select Type--</option>
                                        <option value="Principal">For Redemption (Principal)</option>
                                        <option value="Interest">For Renewal (Interest)</option>
                                    </select>
                                </div>
                                <div class="input_cont">
                                    <label for="penalty">Penalty (Number of Days)<i style="color:red;">*</i></label>
                                    <input type="number" name="penalty" id="penalty" min="0" required disabled></input>
                                </div>
                                <div class="input_cont">
                                    <label for="discount">Discount<i style="color:red;">*</i></label>
                                    <input type="number" name="discount" id="discount" min="0" required disabled></input>
                                </div>
                                <div class="input_cont button_cont">
                                    <button type="submit" name="submit"><img src="../../resources/img/icons/add1.png" alt="add_transaction">Add Transaction</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="../../resources/js/fetch_loan_inner.js"></script>
</body>
</html>