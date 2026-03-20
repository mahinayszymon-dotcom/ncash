<?php
// 1. Include the helper logic
include 'mail_helper.php'; 

echo "Starting TraceMo System Test... <br>";

// 2. Prepare the data (The 'Letter')
$to = 'simahinay5788ant@student.fatima.edu.ph';
$sub = 'N-Cash | Test Confirmation';
$body = "
<div style='background-color: #f4f4f4; padding: 20px; font-family: sans-serif;'>
    <table width='100%' border='0' cellspacing='0' cellpadding='0' style='max-width: 600px; margin: auto; background-color: #ffffff; border-top: 4px solid #8a3333;'>
        <tr>
            <td style='padding: 30px; text-align: center;'>
                <h1 style='color: #1a1a1a; margin: 0; font-size: 24px;'>N-CASH</h1>
                <p style='color: #7a3333; letter-spacing: 2px; text-transform: uppercase; font-size: 12px; margin-top: 5px;'>Luxury Pawnshop</p>
            </td>
        </tr>
        <tr>
            <td style='padding: 0 40px 40px 40px; color: #444444; line-height: 1.6;'>
                <h2 style='color: #1a1a1a;'>Automated Notification</h2>
                <p>This is a test from the <strong>TraceMo System</strong>. If you see this notification, please ignore it.</p>
            </td>
        </tr>
    </table>
</div>
";

// 3. NOW call the function using the variables we just made
$result = sendBusinessEmail($to, $sub, $body);

// 4. Show the result on your screen
if ($result === true) {
    echo "<h1>Success! Email sent to $to.</h1>";
    echo $body; // Preview the luxury layout in your browser
} else {
    echo "<h1>It failed.</h1>";
    echo "Details: " . $result;
}