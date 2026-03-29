<?php 
date_default_timezone_set('Asia/Manila');
$curDate = new DateTime();
$current = $curDate->format('Y-m-d');

$sql = "UPDATE inventory
        SET status = 'Overdue'
        WHERE DATE(due_date) < ?
        AND status = 'Active'";
    
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current);
$stmt->execute();
//pwede ilagay dto ung sa pagdelete ng mga may deletion period na clients

$new_date = new DateTime('yesterday');

if ($new_date->format('N') == 7) {
    $new_date->modify('-1 day');
}

$date_history = $new_date->format('Y-m-d H:i:s');

//check if existing na si date
$sql = "SELECT date FROM balance_history WHERE date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date_history);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) 
{
    $sql = "SELECT end_balance FROM branches WHERE branch_id = 1100";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $mp_branch_bal = htmlspecialchars($row['end_balance']);

    $sql = "SELECT end_balance FROM branches WHERE branch_id = 1101";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $q_branch_bal = htmlspecialchars($row['end_balance']);

    $sql = "SELECT end_balance FROM branches WHERE branch_id = 1102";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $m_branch_bal = htmlspecialchars($row['end_balance']);

    $sql = "INSERT INTO balance_history (date, mp_branch, q_branch, m_branch)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sddd", $date_history, $mp_branch_bal, $q_branch_bal, $m_branch_bal);
    $stmt->execute();
} 

//Checking Liquidated accounts
$sql = "SELECT * FROM inventory WHERE due_date <= DATE_SUB(CURDATE(), INTERVAL 60 DAY) AND status = 'Overdue'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) 
{
    while($lq_row = $result->fetch_assoc())
    {
        $lq_item_id = htmlspecialchars($lq_row['item_id']);
        $lq_c_id = htmlspecialchars($lq_row['client_id']);
        $lq_b_id = htmlspecialchars($lq_row['branch_id']);
        $lq_item_name = htmlspecialchars($lq_row['item_name']);
        $lq_category = htmlspecialchars($lq_row['category']);
        $lq_agreement_num = htmlspecialchars($lq_row['agreement_num']);
        $lq_principal = htmlspecialchars($lq_row['principal']);
        $lq_due_date = htmlspecialchars($lq_row['due_date']);
        $lq_remarks = htmlspecialchars($lq_row['remarks']);
        $lq_item_created = htmlspecialchars($lq_row['created_at']);
        $lq_item_updated = htmlspecialchars($lq_row['updated_at']);
        $lq_creator_uname = htmlspecialchars($lq_row['created_by']);
        $lq_editor_uname = htmlspecialchars($lq_row['updated_by']);
        $lq_interest = htmlspecialchars($lq_row['interest']);
        $lq_is_omit = htmlspecialchars($lq_row['is_omitted']);
        $lq_current = $curDate->format('Y-m-d H:i:s');

        $sql = "SELECT transaction_id, agreement_num, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                FROM transactions
                WHERE item_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $lq_item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) 
        {
            while($lqt_row = $result->fetch_assoc())
            {
                $lq_transac_id = htmlspecialchars($lqt_row['transaction_id']);
                $lq_transac_agreement = htmlspecialchars($lqt_row['agreement_num']);
                $lq_transac_c_id = htmlspecialchars($lqt_row['client_id']);
                $lq_transac_b_id = htmlspecialchars($lqt_row['branch_id']);
                $lq_transac_amt = htmlspecialchars($lqt_row['amount']);
                $lq_transac_type = htmlspecialchars($lqt_row['type_of_pay']);
                $lq_transac_creator = htmlspecialchars($lqt_row['created_by']);
                $lq_transac_c_at = htmlspecialchars($lqt_row['created_at']);
                $lq_transac_e_at = htmlspecialchars($lqt_row['edited_at']);
                $lq_transac_method = htmlspecialchars($lqt_row['method']);
                $lq_transac_p_date = htmlspecialchars($lqt_row['paid_date']);
                $lq_transac_is_link = htmlspecialchars($lqt_row['is_linked']);

                //insert si transac sa archive
                $lq_transac_archiver = "system";
                $lq_transac_reason = "Item has been liquidated";

                $sql = "INSERT INTO transactions_archive (archived_by, archived_date, transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked, reason)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiiiiidssssssis", $lq_transac_archiver, $lq_current, $lq_transac_id, $lq_transac_agreement, $lq_transac_c_id, $lq_transac_b_id, $lq_item_id, $lq_transac_amt, $lq_transac_type, $lq_transac_creator, $lq_transac_c_at, $lq_transac_e_at, $lq_transac_method, $lq_transac_p_date, $lq_transac_is_link, $lq_transac_reason);
                if($stmt->execute())
                {
                    //Delete sa transac
                    $sql = "DELETE FROM transactions WHERE transaction_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $lq_transac_id);
                    $stmt->execute();
                }
            }
        }

        $lq_archiver = "system";
        $lq_archive_reason = "Item has been liquidated";
        $lq_status = "Liquidated";

        //Insert sa liquidations
        $sql = "INSERT INTO items_liquidated (liquidated_at, item_id, client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_at, updated_at, created_by, updated_by, interest) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siiissidsssssssd", $lq_current, $lq_item_id, $lq_c_id, $lq_b_id, $lq_item_name, $lq_category, $lq_agreement_num, $lq_principal, $lq_status, $lq_due_date, $lq_remarks, $lq_item_created, $lq_item_updated, $lq_creator_uname, $lq_editor_uname, $lq_interest);
        if($stmt->execute())
        {
            $sql = "INSERT INTO items_archive (archived_by, item_id, client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_at, updated_at, created_by, updated_by, interest, is_omitted, reason) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiissidsssssssdis", $lq_archiver, $lq_item_id, $lq_c_id, $lq_b_id, $lq_item_name, $lq_category, $lq_agreement_num, $lq_principal, $lq_status, $lq_due_date, $lq_remarks, $lq_item_created, $lq_item_updated, $lq_creator_uname, $lq_editor_uname, $lq_interest, $lq_is_omit, $lq_archive_reason);
            if($stmt->execute())
            {
                //Delete sa inventory
                $sql = "DELETE FROM inventory WHERE item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $lq_item_id);
                $stmt->execute();
            }
        }

        //check if client still has records in inventory (if wala, archive narin si client)
        $sql = "SELECT * FROM inventory WHERE client_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $lq_c_id);
        $stmt->execute();
        $result = $stmt->get_result();

        //if wala nang item si client
        if($result->num_rows == 0)
        {
            $sql = "SELECT fullname, contact, email, address, created_at FROM clients WHERE client_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $lq_c_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0)
            {
                $lqc_row = $result->fetch_assoc();
                $lq_client_name = htmlspecialchars($lqc_row['fullname']);
                $lq_contact = htmlspecialchars($lqc_row['contact']);
                $lq_email = htmlspecialchars($lqc_row['email']);
                $lq_client_addr = htmlspecialchars($lqc_row['address']);
                $lq_c_create_at = htmlspecialchars($lqc_row['created_at']);
            }

            $lq_client_archiver = "system";
            $lq_client_reason = "No more items in the inventory";
            //archive na sya
            $sql = "INSERT INTO clients_archive (archived_by, client_id, fullname, contact, email, address, created_at, reason)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisissss", $lq_client_archiver, $lq_c_id, $lq_client_name, $lq_contact, $lq_email, $lq_client_addr, $lq_c_create_at, $lq_client_reason);
            if($stmt->execute())
            {
                $sql = "DELETE FROM clients WHERE client_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $lq_c_id);
                $stmt->execute();
            }
        }                               
    }
}

//Gets max month of due date
$sql = "SELECT MAX(updated_at) AS last_redeem FROM inventory WHERE status = 'Redeemed'";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) 
{
    $row = $result->fetch_assoc();
    $lastDue_redeem = htmlspecialchars($row['last_redeem']);
}

if(isset($lastDue_redeem))
{
    $lastDate = new DateTime($lastDue_redeem);

    if ($lastDate->format('Y-m') !== $curDate->format('Y-m'))
    {
        $sql = "SELECT * FROM inventory WHERE status = 'Redeemed'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) 
        {
            while($r_row = $result->fetch_assoc())
            {
                $r_item_id = htmlspecialchars($r_row['item_id']);
                $r_c_id = htmlspecialchars($r_row['client_id']);
                $r_b_id = htmlspecialchars($r_row['branch_id']);
                $r_item_name = htmlspecialchars($r_row['item_name']);
                $r_category = htmlspecialchars($r_row['category']);
                $r_agreement_num = htmlspecialchars($r_row['agreement_num']);
                $r_principal = htmlspecialchars($r_row['principal']);
                $r_due_date = htmlspecialchars($r_row['due_date']);
                $r_remarks = htmlspecialchars($r_row['remarks']);
                $r_item_created = htmlspecialchars($r_row['created_at']);
                $r_item_updated = htmlspecialchars($r_row['updated_at']);
                $r_creator_uname = htmlspecialchars($r_row['created_by']);
                $r_editor_uname = htmlspecialchars($r_row['updated_by']);
                $r_interest = htmlspecialchars($r_row['interest']);
                $r_is_omit = htmlspecialchars($r_row['is_omitted']);
                $r_current = $curDate->format('Y-m-d H:i:s');

                $sql = "SELECT transaction_id, agreement_num, client_id, branch_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked
                FROM transactions
                WHERE item_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $r_item_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows > 0) 
                {
                    while($rt_row = $result->fetch_assoc())
                    {
                        $r_transac_id = htmlspecialchars($rt_row['transaction_id']);
                        $r_transac_agreement = htmlspecialchars($rt_row['agreement_num']);
                        $r_transac_c_id = htmlspecialchars($rt_row['client_id']);
                        $r_transac_b_id = htmlspecialchars($rt_row['branch_id']);
                        $r_transac_amt = htmlspecialchars($rt_row['amount']);
                        $r_transac_type = htmlspecialchars($rt_row['type_of_pay']);
                        $r_transac_creator = htmlspecialchars($rt_row['created_by']);
                        $r_transac_c_at = htmlspecialchars($rt_row['created_at']);
                        $r_transac_e_at = htmlspecialchars($rt_row['edited_at']);
                        $r_transac_method = htmlspecialchars($rt_row['method']);
                        $r_transac_p_date = htmlspecialchars($rt_row['paid_date']);
                        $r_transac_is_link = htmlspecialchars($rt_row['is_linked']);

                        //insert si transac sa archive
                        $r_transac_archiver = "system";
                        $r_transac_reason = "Item has been redeemed";

                        $sql = "INSERT INTO transactions_archive (archived_by, archived_date, transaction_id, agreement_num, client_id, branch_id, item_id, amount, type_of_pay, created_by, created_at, edited_at, method, paid_date, is_linked, reason)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssiiiiidssssssis", $r_transac_archiver, $r_current, $r_transac_id, $r_transac_agreement, $r_transac_c_id, $r_transac_b_id, $r_item_id, $r_transac_amt, $r_transac_type, $r_transac_creator, $r_transac_c_at, $r_transac_e_at, $r_transac_method, $r_transac_p_date, $r_transac_is_link, $r_transac_reason);
                        if($stmt->execute())
                        {
                            //Delete sa transac
                            $sql = "DELETE FROM transactions WHERE transaction_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $r_transac_id);
                            $stmt->execute();
                        }
                    }
                }

                $r_archiver = "system";
                $r_archive_reason = "Item has been redeemed";
                $r_status = "Redeemed";

                $sql = "INSERT INTO items_archive (archived_by, item_id, client_id, branch_id, item_name, category, agreement_num, principal, status, due_date, remarks, created_at, updated_at, created_by, updated_by, interest, is_omitted, reason) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siiissidsssssssdis", $r_archiver, $r_item_id, $r_c_id, $r_b_id, $r_item_name, $r_category, $r_agreement_num, $r_principal, $r_status, $r_due_date, $r_remarks, $r_item_created, $r_item_updated, $r_creator_uname, $r_editor_uname, $r_interest, $r_is_omit, $r_archive_reason);
                if($stmt->execute())
                {
                    //Delete sa inventory
                    $sql = "DELETE FROM inventory WHERE item_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $r_item_id);
                    $stmt->execute();
                }

                //check if client still has records in inventory (if wala, archive narin si client)
                $sql = "SELECT * FROM inventory WHERE client_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $r_c_id);
                $stmt->execute();
                $result = $stmt->get_result();

                //if wala nang item si client
                if($result->num_rows == 0)
                {
                    $sql = "SELECT fullname, contact, email, address, created_at FROM clients WHERE client_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $r_c_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if($result->num_rows > 0)
                    {
                        $rc_row = $result->fetch_assoc();
                        $r_client_name = htmlspecialchars($rc_row['fullname']);
                        $r_contact = htmlspecialchars($rc_row['contact']);
                        $r_email = htmlspecialchars($rc_row['email']);
                        $r_client_addr = htmlspecialchars($rc_row['address']);
                        $r_c_create_at = htmlspecialchars($rc_row['created_at']);
                    }

                    $r_client_archiver = "system";
                    $r_client_reason = "No more items in the inventory";
                    //archive na sya
                    $sql = "INSERT INTO clients_archive (archived_by, client_id, fullname, contact, email, address, created_at, reason)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sisissss", $r_client_archiver, $r_c_id, $r_client_name, $r_contact, $r_email, $r_client_addr, $r_c_create_at, $r_client_reason);
                    if($stmt->execute())
                    {
                        $sql = "DELETE FROM clients WHERE client_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $r_c_id);
                        $stmt->execute();
                    }
                }
            }
        }
    }
}
?>