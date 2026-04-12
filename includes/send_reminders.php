<?php
require_once '../resources/external/phpmailer/mail_helper.php'; //para sa PHP mailer
require_once '../resources/external/sms/sms_helper.php'; //para sa SMS or Textbee

date_default_timezone_set('Asia/Manila');
$current = date('Y-m-d');

$sql = "SELECT 
            c.client_id,
            c.fullname, 
            c.email, 
            c.contact,
            i.branch_id,
            DATE_FORMAT(i.due_date, '%M %d, %Y') AS formatted_date,
            GROUP_CONCAT(
                CONCAT(i.agreement_num, '|', i.item_name, '|', i.principal, '|', i.interest) 
                SEPARATOR '||'
            ) AS item_details
        FROM clients AS c
        JOIN inventory AS i ON c.client_id = i.client_id
        WHERE DATEDIFF(DATE(i.due_date), CURRENT_DATE) IN (0, 1, 2, 3, 7)
        AND i.status = 'Active'
        AND NOT EXISTS 
        (
            SELECT 1 
            FROM notifs AS n 
            WHERE n.client_id = c.client_id 
            AND DATE(n.date_sent) = '$current'
            AND n.message LIKE CONCAT('%', i.agreement_num, '%')
        )
        GROUP BY c.client_id, c.fullname, c.email, c.contact, i.branch_id, formatted_date";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $rem_c_id = htmlspecialchars($row['client_id']);
        $rem_b_id = htmlspecialchars($row['branch_id']);
        $rem_name = htmlspecialchars($row['fullname']);
        $rem_email = htmlspecialchars($row['email']);
        $rem_due_date = strtoupper($row['formatted_date']);
        
        //format yung number to +63
        $rem_phone = "+63" . htmlspecialchars($row['contact']);
        
        //for the items of the client
        $items_array = explode('||', $row['item_details']);
        $sms_item_list = "";
        $email_item_list = "<ul>";

        foreach ($items_array as $item) {
            $rem_details = explode('|', $item);
            $rem_a_num = $rem_details[0];
            $rem_i_name = strtoupper($rem_details[1]);
            $rem_principal = number_format($rem_details[2], 2);
            $rem_interest = number_format($rem_details[3], 2);

            //list format ng SMS
            $sms_item_list .= "#$rem_a_num $rem_i_name @$rem_principal.\n EXTENSION FEE Php $rem_interest\n\n";
            
            //list format ng email
            $email_item_list .= "<li>#$rem_a_num $rem_i_name @$rem_principal. (Extension Fee: Php $rem_interest)</li>";
        }
        $email_item_list .= "</ul>";

        //creating the body of the SMS
        $sms_body = "GENTLE REMINDER FROM N-CASH\n";
        $sms_body .= "Good day, $rem_name!\n";
        $sms_body .= "We would like to remind you of your account with us.\n";
        $sms_body .= $sms_item_list;
        $sms_body .= "*PLEASE BE REMINDED, THERE WILL BE ADDITIONAL CHARGE FOR LATE PAYMENT.\n";
        $sms_body .= "Please pay on or before the due date.\n";
        $sms_body .= "$rem_due_date\n";
        $sms_body .= "*SUNDAY / CLOSED\n\n";
        $sms_body .= "PLS. CONFIRM OR SENT YOUR PROOF OF PAYMENT ON THIS NUMBER\n";
        $sms_body .= "09392095180 (SMART / VIBER)\n";
        $sms_body .= "09175341811 (GLOBE)\n";
        $sms_body .= "89943240 (LANDLINE)\n";
        $sms_body .= "(MS. CRISEL)\n\n";
        $sms_body .= "*KINDLY DISREGARD THIS MESSAGE IF ACCOUNT IS UP- TO - DATE.\n";
        $sms_body .= "THANK YOU! GOD BLESS!";

        //creating the body of the email
        $email_body = "<h3>GENTLE REMINDER FROM N-CASH</h3>
                      <p>Good day, $rem_name!</p>
                      <p>We would like to remind you of your account with us:</p>
                      $email_item_list
                      <p><b>*PLEASE BE REMINDED, THERE WILL BE ADDITIONAL CHARGE FOR LATE PAYMENT.</b></p>
                      <p>Please pay on or before the due date: <b>$rem_due_date</b></p>
                      <p><b>PLS. CONFIRM OR SENT YOUR PROOF OF PAYMENT ON THIS NUMBER:</b><br>
                      09392095180 (SMART / VIBER)<br>
                      09175341811 (GLOBE)<br>
                      89943240 (LANDLINE)<br>
                      (MS. CRISEL)</p>
                      <p><i>*KINDLY DISREGARD THIS MESSAGE IF ACCOUNT IS UP- TO - DATE.</i></p>
                      <p>THANK YOU! GOD BLESS!</p>";

        $subject = "Reminder for Renewal - N-Cash Luxury Pawnshop";
        
        //function to send emails
        $email_res = sendBusinessEmail($rem_email, $subject, $email_body);
        if ($email_res === true) {
            logToDB($conn, $rem_b_id, $rem_c_id, $email_body, 'Email');
        }

        //function to send sms
        if (sendNotificationSMS($rem_phone, $sms_body)) {
            logToDB($conn, $rem_b_id, $rem_c_id, $sms_body, 'SMS');
        }
    }
}

function logToDB($conn, $branchId, $clientId, $message, $type) {
    $rem_status = 'Sent';
    $stmt = $conn->prepare("INSERT INTO notifs (branch_id, client_id, message, type, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $branchId, $clientId, $message, $type, $rem_status);
    $stmt->execute();
    $stmt->close();
}
?>