<?php
// 1. Get the current script path (e.g., /ncash-tracemo/dashboard/settings.php)
$current_page = $_SERVER['PHP_SELF'];

// 2. Define the sub-pages that require the "Special" nav layout
// We use BASE_PATH here because PHP_SELF is a path, not a full URL
$sub_pages = [
    BASE_PATH . "dashboard/transactions/add_dual.php",
    BASE_PATH . "dashboard/logs/audit_trail.php",
    BASE_PATH . "dashboard/message_logs/details.php",
    BASE_PATH . "dashboard/inventory/pending_disposition.php",
    BASE_PATH . "dashboard/transactions/balance_audit.php",
    BASE_PATH . "dashboard/staff/info.php",
    BASE_PATH . "dashboard/transactions/transaction_details.php",
    BASE_PATH . "dashboard/transactions/add.php",
    BASE_PATH . "dashboard/inventory/add.php",
    BASE_PATH . "dashboard/inventory/item_details.php",
    BASE_PATH . "dashboard/settings/account.php",
    BASE_PATH . "dashboard/settings/activity_logs.php",
    BASE_PATH . "dashboard/settings/preferences.php",
    BASE_PATH . "dashboard/settings/security.php",
    BASE_PATH . "dashboard/settings/system.php"
];

// 3. Determine if we are on a sub-page
$is_sub_page = in_array($current_page, $sub_pages);
?>

<div class="nav_logo">
    <a href="<?php echo BASE_URL; ?>dashboard/">
        <img src="<?php echo BASE_URL; ?>resources/img/ncash_logo_main.png" alt="NCash">
    </a>
</div>

<div class="nav_links">
    <ul>
        <li>MAIN</li>
        <li class="links">
            <a href="<?php echo BASE_URL; ?>dashboard/">
                <img src="<?php echo BASE_URL; ?>resources/img/icons/dashboard.png" alt="dashboard">
                <p>Dashboard</p>
            </a>
        </li>
        <li class="links">
            <a href="<?php echo BASE_URL; ?>dashboard/inventory.php">
                <img src="<?php echo BASE_URL; ?>resources/img/icons/inventory.png" alt="inventory">
                <p>Inventory</p>
            </a>
        </li>
        <li class="links">
            <a href="<?php echo BASE_URL; ?>dashboard/transactions.php">
                <img src="<?php echo BASE_URL; ?>resources/img/icons/transactions.png" alt="transactions">
                <p>Transactions</p>
            </a>
        </li>
        <li class="links">
            <a href="<?php echo BASE_URL; ?>dashboard/message_logs.php">
                <img src="<?php echo BASE_URL; ?>resources/img/icons/message.png" alt="logs">
                <p>Message Logs</p>
            </a>
        </li>
        <br>
        <li>ACCOUNT</li>
        <li class="links">
            <a href="<?php echo BASE_URL; ?>dashboard/settings.php">
                <img src="<?php echo BASE_URL; ?>resources/img/icons/settings.png" alt="settings">
                <p>Settings</p>
            </a>
        </li>
    </ul>
</div>

<?php if (isset($_SESSION['role']) && $_SESSION['role'] === "admin"): ?>
    <div class="nav_links_admin">
        <ul>
            <li>ADMINISTRATION</li>
            <li class="links">
                <a href="<?php echo BASE_URL; ?>dashboard/reports.php">
                    <img src="<?php echo BASE_URL; ?>resources/img/icons/reports.png" alt="reports">
                    <p>Reports</p>
                </a>
            </li>
            <li class="links">
                <a href="<?php echo BASE_URL; ?>dashboard/staff_management.php">
                    <img src="<?php echo BASE_URL; ?>resources/img/icons/management.png" alt="management">
                    <p>Staff Management</p>
                </a>
            </li>
        </ul>
    </div>
<?php endif; ?>