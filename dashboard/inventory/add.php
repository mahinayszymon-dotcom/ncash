<?php
ob_start();
include("../../config/session_check.php");
include("../../config/db_conn.php");
include("../../db/branch_fetch.php");

date_default_timezone_set('Asia/Manila');
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
    <link rel="stylesheet" href="../../resources/css/pages/dashboard/inventory.css">
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
                        <h1>Add Item</h1>
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
                            <h2>Add New Item</h2>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../inventory.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    <div class="data_controls_form">
                        <?php
                            if(isset($_POST['submit']))
                            {
                                if($role === "admin")
                                {
                                    $branch_id = htmlspecialchars($_POST['branch_select']);
                                }

                                $agreement_num = htmlspecialchars($_POST['agreement_num']);
                                $principal =  trim(htmlspecialchars($_POST['principal']));
                                $rate_input = trim(htmlspecialchars($_POST['interest']));
                                $fullname = trim(ucwords(htmlspecialchars($_POST['fullname'])));
                                $contact = trim(htmlspecialchars($_POST['contact']));
                                $email = trim(htmlspecialchars($_POST['email']));
                                $item_name =  trim(ucwords(htmlspecialchars($_POST['item_name'])));
                                $remarks =  trim(htmlspecialchars($_POST['remarks']));

                                $sql = "SELECT * FROM inventory WHERE agreement_num = ? AND branch_id = ?";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("ii", $agreement_num, $branch_id);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if($result->num_rows > 0)
                                {
                                    $_SESSION['error_msg'] =  "Agreement number already exists!";
                                }
                                else
                                {
                                    if(empty($principal) || empty($rate_input) || empty($fullname) || empty($contact) || empty($email) || empty($item_name) || empty($remarks))
                                    {
                                        $_SESSION['error_msg'] = "Please fill out all input fields.";
                                    }
                                    else 
                                    {
                                        if($principal > 0)
                                        {
                                            if($rate_input > 0)
                                            {
                                                $sql = "SELECT * FROM clients WHERE fullname LIKE CONCAT('%', ?, '%') AND (contact = ? OR email = ?)";
                                                $stmt = $conn->prepare($sql);
                                                $stmt->bind_param("sis", $fullname, $contact, $email);
                                                $stmt->execute();
                                                $result = $stmt->get_result();

                                                if($result->num_rows > 0)
                                                {
                                                    while($row = $result->fetch_assoc())
                                                    {
                                                        $client_id = htmlspecialchars($row['client_id']);
                                                    }
                                                }
                                                else
                                                {
                                                    $sql = "SELECT client_id, fullname, contact, email, address, created_at FROM clients_archive WHERE fullname LIKE CONCAT('%', ?, '%') AND (contact = ? OR email = ?)";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("sis", $fullname, $contact, $email);
                                                    $stmt->execute();
                                                    $fetch_result = $stmt->get_result();

                                                    if($fetch_result->num_rows > 0)
                                                    {
                                                        $fetch_row = $fetch_result->fetch_assoc();
                                                        $fetch_c_id = htmlspecialchars($fetch_row['client_id']);
                                                        $f_client_name = htmlspecialchars($fetch_row['fullname']);
                                                        $f_contact = htmlspecialchars($fetch_row['contact']);
                                                        $f_email = htmlspecialchars($fetch_row['email']);
                                                        $f_client_addr = htmlspecialchars($fetch_row['address']);
                                                        $f_create_at = htmlspecialchars($fetch_row['address']);

                                                        $client_id = $fetch_c_id;

                                                        $sql = "INSERT INTO clients (client_id, fullname, contact, email, address, created_at) 
                                                                VALUES (?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("isisss", $fetch_c_id, $f_client_name, $f_contact, $f_email, $f_client_addr, $f_create_at);
                                                        if($stmt->execute())
                                                        {
                                                            $sql = "DELETE FROM clients_archive WHERE client_id = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("i", $fetch_c_id);
                                                            $stmt->execute();
                                                        }   
                                                    }
                                                    else 
                                                    {
                                                        $address = htmlspecialchars($_POST['address']);

                                                        $sql = "INSERT INTO clients (fullname, contact, email, address) VALUES (?,?,?,?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("siss", $fullname, $contact, $email, $address);
                                                        
                                                        if($stmt->execute())
                                                        {
                                                            $client_id = $conn->insert_id;
                                                        }
                                                    }
                                                }

                                                if(isset($client_id))
                                                {
                                                    $category =  htmlspecialchars($_POST['category']);
                                                    $inp_date =  htmlspecialchars($_POST['due_date']);
                                                    
                                                    $inputDate = new DateTime($inp_date);
                                                    $curDate = new DateTime();
                                                    $curDate->setTime(0, 0);
                                                    if($inputDate < $curDate)
                                                    {
                                                        $status = "Overdue";
                                                    }
                                                    else
                                                    {
                                                        $status = "Active";
                                                    }

                                                    $is_omit = (isset($_POST['omit_transaction'])) ? 1 : 0;

                                                    $rate = $rate_input / 100;
                                                    $interest = $principal * $rate;
                                                    $creator = $_SESSION['username'];

                                                    $due_date = $inputDate->format('Y-m-d H:i:s');

                                                    $sql = "SELECT end_balance FROM branches WHERE branch_id = ?";
                                                    $stmt = $conn->prepare($sql);
                                                    $stmt->bind_param("i", $branch_id);
                                                    $stmt->execute();
                                                    $result = $stmt->get_result();
                                                    $row = $result->fetch_assoc();

                                                    $fetch_eb = (float)htmlspecialchars($row['end_balance']);
                                                    $upd_success = false;

                                                    if(isset($fetch_eb) && $is_omit == 0)
                                                    {
                                                        $new_eb_val = ($fetch_eb - (float)$principal) + (float)$interest; //advance interest
                                                        $sql = "UPDATE branches
                                                                SET end_balance = ?
                                                                WHERE branch_id = ? ";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("di", $new_eb_val, $branch_id);
                                                        if($stmt->execute())
                                                        {
                                                            $upd_success = true;
                                                        }
                                                    }
                                                    else 
                                                    {
                                                        $upd_success = true;
                                                    }
                                                    
                                                    if($upd_success)
                                                    {
                                                        $sql = "INSERT INTO inventory (client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_by, updated_by, interest, is_omitted)
                                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("iissidsssssdi", $client_id, $branch_id, $item_name, $category, $agreement_num, $principal, $status, $due_date, $remarks, $creator, $creator, $interest, $is_omit);

                                                        if($stmt->execute())
                                                        {
                                                            $audit_u_id = $_SESSION['user_id'];
                                                            $audit_action = "Created";
                                                            $audit_obj = "Item";
                                                            $audit_desc = "Created item '$item_name' with agreement no. $agreement_num on inventory";

                                                            $curDate = new DateTime();
                                                            $current = $curDate->format('Y-m-d H:i:s');

                                                            $sql = "INSERT INTO audit_trail (user_id, action, object_type, description, branch_id, timestamp)
                                                                    VALUES (?, ?, ?, ?, ?, ?)";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("isssis", $audit_u_id, $audit_action, $audit_obj, $audit_desc, $branch_id, $current);
                                                            if($stmt->execute())
                                                            {
                                                                $_SESSION['success_msg'] = "Item Successfully Added!";
                                                            }
                                                        }
                                                        else
                                                        {
                                                            $_SESSION['error_msg'] = "Error:" . $stmt->error;
                                                        }
                                                    }
                                                    else 
                                                    {
                                                        $_SESSION['error_msg'] = "Error occurred while inserting item.";
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $_SESSION['error_msg'] = "Interest Rate should be greater than 0.";
                                            }
                                        }
                                        else
                                        {
                                            $_SESSION['error_msg'] = "Principal should be greater than 0.";
                                        }
                                    }
                                }
                            }
                            ob_end_flush();
                        ?>
                        <hr>
                        <div class="form">        
                            <form action="add.php" method="POST">
                                <div class="fullwidth">       
                                    <p>Client Information</p>
                                </div>
                                <?php
                                    $agreement_num = 1;

                                    if (isset($branch_id)) 
                                    {
                                        $sql = "SELECT MAX(agreement_num) AS max_agreement FROM inventory WHERE branch_id = ?";
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bind_param("i", $branch_id);
                                        $stmt->execute();
                                        $result = $stmt->get_result();

                                        if($result->num_rows > 0) 
                                        {
                                            $row = $result->fetch_assoc();
                                            $max_num = ($row['max_agreement'] ?? 0);

                                            $sql = "SELECT MAX(agreement_num) AS max_archive FROM items_archive WHERE branch_id = ?";
                                            $stmt = $conn->prepare($sql);
                                            $stmt->bind_param("i", $branch_id);
                                            $stmt->execute();
                                            $fetch_result = $stmt->get_result();

                                            if($fetch_result->num_rows > 0) 
                                            {
                                                $fetch_row = $fetch_result->fetch_assoc();
                                                $a_agreement = ($fetch_row['max_archive'] ?? 0);
                                            }

                                            if($max_num > $a_agreement)
                                            {
                                                $agreement_num = $max_num + 1;
                                            }
                                            else 
                                            {
                                                $agreement_num = $a_agreement + 1;
                                            }
                                        }

                                        $stmt->close();
                                    }
                                ?>
                                <div class="input_cont">
                                    <label for="agreement_num" title="Agreement number is a unique identification number for this item">Agreement Number</label>
                                    <input type="text" name="agreement_num" id="agreement_num" pattern="[0-9]*" value="<?php echo $agreement_num ?>" readonly>
                                </div>
                                <div class="input_cont">
                                    <label for="fullname">Client Name<i style="color:red;">*</i></label>
                                    <input type="text" name="fullname" id="fullname" placeholder="Ex. John Anthony Santos" pattern="[A-Za-z ]+" required>
                                </div>
                                <div class="input_cont">
                                    <label for="contact">Contact No.<i style="color:red;">*</i></label>
                                    <input type="text" name="contact" id="contact" inputmode="numeric" placeholder="eg. 9123456789" pattern="[9]{1}[0-9]{2}[0-9]{3}[0-9]{4}" required>
                                </div>
                                <div class="input_cont">
                                    <label for="email">Email<i style="color:red;">*</i></label>
                                    <input type="email" name="email" id="email" required>
                                </div>
                                <?php
                                    if ($role === "admin") {
                                        echo '<div class="input_cont">
                                                <label for="address">Address<i style="color:red;">*</i></label>
                                                <input type="text" style="resize: none;"id="address" name="address" required></input>
                                            </div>';
                                        echo '<div class="input_cont">
                                                <label for="branch_select">Branch<i style="color:red;">*</i></label>
                                                <select name="branch_select" id="branch_select" required onchange="updateAgreement()">
                                                    <option value="" disabled selected>--Select Branch--</option>
                                                    <option value="1100">Marikina-Pasig Branch</option>
                                                    <option value="1101">Quezon City Branch</option>
                                                    <option value="1102">Makati Branch</option>
                                                </select>
                                            </div>';
                                    } else {
                                        echo '<div class="fullwidth">
                                                <label for="address">Address<i style="color:red;">*</i></label>
                                                <input type="text" style="resize: none;" name="address" id="address" required></input>
                                            </div>';
                                    }
                                ?>
                                <div class="fullwidth checkbox_cont">
                                    <input type="checkbox" id="omit_transaction" name="omit_transaction" value="1">
                                    <label for="exclude_transaction">Omit this transaction from business reports.</label>
                                </div>
                                
                                <div class="fullwidth">
                                    <p>Item Information</p>
                                </div>
                                <div class="input_cont">
                                    <label for="item_name">Item Name<i style="color:red;">*</i></label>
                                    <input type="text" id="item_name" name="item_name" required>
                                </div>
                                <div class="input_cont">
                                    <label for="category">Category<i style="color:red;">*</i></label>
                                    <select name="category" id="category" required>
                                        <option value="" disabled selected>--Select Category--</option>
                                        <option value="Personal Accessories">Personal Accessories</option>
                                        <option value="Electronic Gadgets">Electronic Gadgets</option>
                                        <option value="Vehicles">Vehicles</option>
                                        <option value="Real Estate Property">Real Estate Property</option>
                                    </select>
                                </div>
                                <div class="input_cont">
                                    <label for="principal">Principal<i style="color:red;">*</i></label>
                                    <input type="text" name="principal" id="principal" pattern="[0-9]*" required>
                                </div>
                                <div class="input_cont">
                                    <label for="interest">Interest Rate (%)<i style="color:red;">*</i></label>
                                    <input type="text" name="interest" id="interest" pattern="[0-9]*" required>
                                </div>
                                <div class="input_cont">
                                    <label for="due_date">Due Date<i style="color:red;">*</i></label>
                                    <input type="date" name="due_date" id="due_date" required>
                                </div>
                                <div class="input_cont">
                                    <label for="remarks">Remarks<i style="color:red;">*</i></label>
                                    <input type="text" name="remarks" id="remarks" required></input>
                                </div>
                                <div class="input_cont">
                                    <div class="result_cont">
                                        <?php
                                            if (isset($_SESSION['success_msg'])) {
                                                $redirect_url = "../../dashboard/inventory.php";
                                                $delay = 0.1; // three seconds muna taymperst

                                                // echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['success_msg'] . "</span>";

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
                                <div class="input_cont button_cont">
                                    <button type="submit" name="submit"><img src="../../resources/img/icons/add1.png" alt="add_transaction">Add Item</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="../../resources/js/fetch_agreement.js"></script>
</body>
</html>