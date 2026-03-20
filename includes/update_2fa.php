<?php

require_once("../config/session_check.php");

// 2. MUST include the database connection to use $conn
// NOTE: Verify this path! Depending on exactly where update_2fa.php is, 
// you might need "../config/db_conn.php" or "../../config/db_conn.php"
require_once("../config/db_conn.php");

// 3. Process the POST request from the modal form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    
    // 4. Validate Input: Ensure it exists and is strictly '0' or '1'
    if (isset($_POST['tfa_status']) && in_array($_POST['tfa_status'], ['0', '1'], true)) {
        $status = (int) $_POST['tfa_status'];

        // 5. Check if a 2FA record already exists for this user (Using correct MySQLi syntax)
        $stmt = $conn->prepare("SELECT id FROM user_two_factor WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recordExists = $result->fetch_assoc();
        $stmt->close(); // Close the statement!

        if ($recordExists) {
            // A row exists, so we UPDATE it
            $updateStmt = $conn->prepare("UPDATE user_two_factor SET is_enabled = ?, updated_at = NOW() WHERE user_id = ?");
            $updateStmt->bind_param("ii", $status, $user_id);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // No row exists yet, so we INSERT a new one
            $insertStmt = $conn->prepare("INSERT INTO user_two_factor (user_id, is_enabled) VALUES (?, ?)");
            $insertStmt->bind_param("ii", $user_id, $status);
            $insertStmt->execute();
            $insertStmt->close();
        }

        // 6. Set a success message and redirect back to the security settings page
        // Ensure this path correctly points back to your settings page!
        $_SESSION['toast_message'] = "Two-Factor Authentication settings updated successfully!";
        header("Location: ../dashboard/settings/security.php"); 
        exit();

    } else {
        // Handle invalid input 
        $_SESSION['toast_error'] = "Invalid selection. Please try again.";
        header("Location: ../../auth/error.php");
        exit();
    }
} else {
    // If someone tries to visit update_2fa.php directly via the URL, send them away
    header("Location: ../../auth/error.php");
    exit();
}
?>