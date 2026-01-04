<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || !isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$fullname = $_SESSION['fullname'];
$email = $_SESSION['email'];
$role = $_SESSION['role'];
$branch = $_SESSION['branch_id'];
$status = $_SESSION['status'];
$created_at = $_SESSION['created_at'];

$created_at_date = date("Y-m-d", strtotime($created_at));

# branch code
if ($branch == "1100") {
    $branch_name = "Marikina-Pasig";
} else if ($branch == "1101") {
    $branch_name = "Quezon City";
} else if ($branch == "1102") {
    $branch_name = "Makati City";
} else {
    $branch_name = "Unknown";
}
?>
<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted || performance.getEntriesByType("navigation")[0].type === "back_forward") {
        window.location.reload();
    }
});
</script>