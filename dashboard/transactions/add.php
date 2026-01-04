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
                            <form id="notif-form" action="" method="GET">
                                <button type="submit"><img src="../../resources/img/icons/notif.png" alt="notifications"></button>
                            </form>        
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
                            <button onclick="window.location.href='../transactions.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <hr>
                    <div class="data_controlsC">
                        <div class="data_controls_form">
                            <div class="form">   
                                <?php
                                    if(isset($_POST['submit']))
                                    {
                                        date_default_timezone_set('Asia/Manila');

                                        $selected_id =  htmlspecialchars($_POST['agreement_num']);
                                        $type_of_pay = htmlspecialchars($_POST['type_of_payment']);
                                        $method = htmlspecialchars($_POST['mode_of_payment']);
                                        $amount = trim(htmlspecialchars($_POST['amount']));
                                        $success = false;

                                        $curDate = new DateTime();
                                        
                                        $sql = "SELECT due_date FROM inventory WHERE item_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $selected_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();
                                        if($result->num_rows > 0)
                                        {
                                            $row = $result->fetch_assoc();
                                            $date_str = htmlspecialchars($row['due_date']);
                                            $dueDate = new DateTime($date_str);
                                        }
                                        
                                        if($type_of_pay == "Principal")
                                        {
                                            $principal = htmlspecialchars($_POST['principal']);
                                            $trim = trim($principal, '₱ ');
                                            $current = $curDate->format('Y-m-d H:i:s');

                                            if($amount == $trim)
                                            {
                                                $sql = "UPDATE inventory
                                                        SET status = 'Redeemed', updated_at = ?
                                                        WHERE item_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("si", $current, $selected_id);
                                                if($stmt->execute())
                                                {
                                                    $success = true;
                                                }
                                                else
                                                {
                                                    $_SESSION['error_msg'] = "Error:" . $stmt->error;
                                                }
                                            }
                                            else
                                            {
                                                $_SESSION['error_msg'] = "Amount should be equal to selected type of payment.";
                                            }
                                        }
                                        else if($type_of_pay == "Interest")
                                        {
                                            $interest = htmlspecialchars($_POST['interest']);
                                            $trim = trim($interest, '₱ ');
                                            if($amount == $trim)
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
                                                
                                                $sql = "UPDATE inventory
                                                        SET due_date = ?, status = 'Active'
                                                        WHERE item_id = ?";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("si", $newDate, $selected_id);
                                                if($stmt->execute())
                                                {
                                                    $success = true;
                                                }
                                                else
                                                {
                                                    $_SESSION['error_msg'] = "Error:" . $stmt->error;
                                                }
                                            }
                                            else
                                            {
                                                $_SESSION['error_msg'] = "Amount should be equal to selected type of payment.";
                                                header('Location: ' . $_SERVER['PHP_SELF']);
                                                exit();
                                            }
                                        }

                                        if(isset($success) && $success == true)
                                        {
                                            $sql = "SELECT branch_id, client_id, agreement_num FROM inventory WHERE item_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $selected_id);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                
                                            if($result->num_rows > 0)
                                            {
                                                $row = $result->fetch_assoc();
                                                $item_branch = htmlspecialchars($row['branch_id']);
                                                $client_id = htmlspecialchars($row['client_id']);
                                                $agreement_num = htmlspecialchars($row['agreement_num']);
                                            }

                                            if(isset($client_id) && isset($selected_id))
                                            {
                                                $creator = $_SESSION['username'];
                                                $sql = "INSERT INTO transactions (agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, method, paid_date)
                                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("iiiidssss", $agreement_num, $client_id, $item_branch, $selected_id, $amount, $type_of_pay, $creator, $method, $date_str);
                                                if($stmt->execute())
                                                {
                                                    $_SESSION['transac_success_msg'] = "Transaction has been added and recorded successfully!";
                                                }
                                            }
                                        }
                                    } 
                                    ob_end_flush();
                                ?>     
                                <form action="add.php" method="POST">
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
                                        <label for="mode_of_payment">Method of Payment<i style="color:red;">*</i></label>
                                        <select name="mode_of_payment" id="mode_of_payment" required>
                                            <option value="" disabled selected>--Select Method--</option>
                                            <option value="Cash">Cash</option>
                                            <option value="Online">Online</option>
                                            <option value="Bank">Bank</option>
                                        </select>
                                    </div>
                                    <div class="input_cont">
                                        <label for="type_of_payment">Type of Payment<i style="color:red;">*</i></label>
                                        <select name="type_of_payment" id="type_of_payment" required>
                                            <option value="" disabled selected>--Select Type--</option>
                                            <option value="Principal">For Redemption (Principal)</option>
                                            <option value="Interest">For Renewal (Interest)</option>
                                        </select>
                                    </div>
                                    <div class="input_cont">
                                        <label for="amount">Amount<i style="color:red;">*</i></label>
                                        <input type="text" name="amount" id="amount"  pattern="[0-9]*" required></input>
                                    </div>
                                    <div class="input_cont button_cont">
                                        <button type="submit" name="submit"><img src="../../resources/img/icons/add1.png" alt="add_transaction">Add Transaction</button>
                                    </div>
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
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="../../resources/js/fetch_loan_inner.js"></script>
</body>
</html>