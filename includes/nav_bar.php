<?php
    $current_page = $_SERVER['PHP_SELF'];
    // echo '<h1 style="position:absolute; top: 0; left: 0;">' . $current_page. '</h1>';

    if ($current_page === "/ncash-tracemo/dashboard/staff/info.php" || $current_page === "/ncash-tracemo/dashboard/transactions/transaction_details.php" || $current_page === "/ncash-tracemo/dashboard/transactions/add.php" || $current_page === "/ncash-tracemo/dashboard/inventory/add.php" || $current_page === "/ncash-tracemo/dashboard/inventory/item_details.php" || $current_page === "/ncash-tracemo/dashboard/settings/account.php" || $current_page === "/ncash-tracemo/dashboard/settings/activity_logs.php" || $current_page === "/ncash-tracemo/dashboard/settings/preferences.php" || $current_page === "/ncash-tracemo/dashboard/settings/security.php" || $current_page === "/ncash-tracemo/dashboard/settings/system.php") {
        echo '<div class="nav_logo">
            <a href="../../dashboard/"><img src="../../resources/img/ncash_logo_main.png" alt="NCash"></a>
        </div>
        <div class="nav_links">
            <ul>
                <li>MAIN</li>
                <li class="links">
                    <a href="../../dashboard/">
                        <img src="../../resources/img/icons/dashboard.png" alt="dashboard">
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="links">
                    <a href="../../dashboard/inventory.php">
                        <img src="../../resources/img/icons/inventory.png" alt="inventory">
                        <p>Inventory</p>
                    </a>
                </li>
                <li class="links">
                    <a href="../../dashboard/transactions.php">
                        <img src="../../resources/img/icons/transactions.png" alt="transactions">
                        <p>Transactions</p>
                    </a>
                </li>
                <li class="links">
                    <a href="../../dashboard/message_logs.php">
                        <img src="../../resources/img/icons/message.png" alt="logs">
                        <p>Message Logs</p>
                    </a>
                </li>
                <BR></BR>
                <li>ACCOUNT</li>
                <li class="links">
                    <a href="../../dashboard/settings.php">
                        <img src="../../resources/img/icons/settings.png" alt="settings">
                        <p>Settings</p>
                    </a>
                </li>
            </ul>
        </div>
        ';

        if ($_SESSION['role'] === "admin")
                    {
                        echo '<div class="nav_links_admin">
                    <ul>
                        <li>ADMINISTRATION</li>
                        <li class="links">
                            <a href="../../dashboard/reports.php">
                                <img src="../../resources/img/icons/reports.png" alt="reports">
                                <p>Reports</p>
                            </a>
                        </li>
                        <li class="links">
                            <a href="../../dashboard/staff_management.php">
                                <img src="../../resources/img/icons/management.png" alt="management">
                                <p>Staff Management</p>
                            </a>
                        </li>
                    </ul>
                </div>';
                    }

            
        } else {
            echo '<div class="nav_logo">
                        <a href="../dashboard/"><img src="../resources/img/ncash_logo_main.png" alt="NCash"></a>
                    </div>
                    <div class="nav_links">
                        <ul>
                            <li>MAIN</li>
                            <li class="links">
                                <a href="../dashboard/">
                                    <img src="../resources/img/icons/dashboard.png" alt="dashboard">
                                    <p>Dashboard</p>
                                </a>
                            </li>
                            <li class="links">
                                <a href="../dashboard/inventory.php">
                                    <img src="../resources/img/icons/inventory.png" alt="inventory">
                                    <p>Inventory</p>
                                </a>
                            </li>
                            <li class="links">
                                <a href="../dashboard/transactions.php">
                                    <img src="../resources/img/icons/transactions.png" alt="transactions">
                                    <p>Transactions</p>
                                </a>
                            </li>
                            <li class="links">
                                <a href="../dashboard/message_logs.php">
                                    <img src="../resources/img/icons/message.png" alt="logs">
                                    <p>Message Logs</p>
                                </a>
                            </li>
                            <BR></BR>
                            <li>ACCOUNT</li>
                            <li class="links">
                                <a href="../dashboard/settings.php">
                                    <img src="../resources/img/icons/settings.png" alt="settings">
                                    <p>Settings</p>
                                </a>
                            </li>
                        </ul>
                    </div>';
                    if ($_SESSION['role'] === "admin")
                    {
                        include('../includes/nav_bar_admin.php');
                    }
        }

?>