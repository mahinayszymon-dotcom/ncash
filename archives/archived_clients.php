<?php
include("../config/session_check.php");  // pang check ng session
include("../config/db_conn.php");   // pang connect sa db
include("../db/branch_fetch.php"); // para kunin ung related sa branch

$_SESSION['previous_link'] = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived Clients</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/inventory.css">
    <link rel="stylesheet" href="../resources/css/pages/archives/archive.css">
    <style>
        .nav_links ul li:nth-child(3) {
            background-color: transparent;
            opacity: 0.8;
        }

        .nav_links ul li:nth-child(3) img {
            opacity: 0.8;
        }

        .archive_nav_links a:nth-child(3) {
            background-color: var(--blue) !important;
            color: var(--main-content) !important;
            opacity: 1;
        }

        .archive_nav_links a:nth-child(3):hover {
            border: none;
            border-radius: 5px;
            /* background-color: var(--red-dark) !important; */
            color: var(--main-content) !important;
            opacity: 1;
        }
    </style>
</head>
<body>
    <main>
        <section class="dashboard">
            <section class="navigation_bar">
                <?php
                    include('../includes/nav_bar.php')
                ?>
            </section>
            <section class="main_content">
                <?php
                    include('../includes/top_panel.php')
                ?>
                <div class="central_panelC">
                    <div class="data_tableC">
                        <div class="data_panel_subheaderC">
                            <div class="archive_nav_links">
                                <a href="archived_transactions.php">Transactions</a>
                                <a href="archived_items.php">Items</a>
                                <a href="archived_clients.php">Clients</a>
                                <a href="liquidated_items.php">Liquidated</a>
                            </div>
                        </div>
                        <br>
                        <div class="data_panel_header">
                            <div class="data_panel_name">
                                <div class="icon_normal">
                                    <img src="../resources/img/icons/archive_w.png" alt="table_icon">
                                </div>
                                <h2>Archived Data Tabulation</h2>
                            </div>
                            <div class="data_panel_buttonsC">
                                <?php 
                                    include '../includes/search_bar.php'; 
                                
                                    $searchColumns = [
                                        'client_id',
                                        'fullname',
                                        'contact',
                                        'email',
                                        'address',
                                        'created_at'
                                    ];

                                    $where = [];

                                    include '../includes/search_handler.php';

                                    $role = $_SESSION['role'];
                                    date_default_timezone_set('Asia/Manila');
                                ?>
                                <form action="archived_clients.php" method="GET">
                                    <span class="custom-arrow-sort"><img src="../resources/img/icons/filter.png" alt="filter"></span>
                                    <select name="branch" id="branch" onchange="this.form.submit()" class="sort">
                                        <?php
                                            $selected = $_GET['branch'] ?? 'default'; 
                                        ?>
                                        <option value="all" <?= $selected === 'default' ? 'selected' : '' ?>>Default Sorting</option>
                                        <option value="nameAZ" <?= $selected === 'nameAZ' ? 'selected' : '' ?>>Name (A-Z)</option>
                                        <option value="nameZA" <?= $selected === 'nameZA' ? 'selected' : '' ?>>Name (Z-A)</option>
                                    </select>
                                    <span class="custom-arrow"><img src="../resources/img/icons/arrow_drop_down_bb.png" alt="sort"></span>
                                </form>
                                <?php
                                    $sorting = isset($_GET['branch']) ? $_GET['branch'] : 'default';
                                    $orderBy = '';

                                    switch ($sorting)
                                    {
                                        case 'all':
                                        case 'default':
                                            $orderBy = " ORDER BY ca.archived_date DESC";
                                            break;
                                        case 'nameAZ': 
                                            $orderBy = " ORDER BY ca.fullname ASC";
                                            break;
                                        case 'nameZA': 
                                            $orderBy = " ORDER BY ca.fullname DESC";
                                            break;
                                        default:
                                            $orderBy = '';
                                            break;
                                    }

                                    $where_sql = '';
                                    if (!empty($where)) 
                                    {
                                        // $sql .= " WHERE " . implode(" AND ", $where);
                                        $where_sql = " WHERE " . implode(" AND ", $where);
                                    }
                                    /*Count*/
                                    
                                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                    $limit = 10;
                                    $offset = ($page - 1) * $limit;

                                    $sql = "SELECT ca.client_id, ca.fullname, ca.contact, ca.email, ca.address, ca.created_at
                                        FROM clients_archive AS ca
                                        $where_sql
                                        $orderBy
                                        LIMIT $limit OFFSET $offset";
                                        //$where_sql

                                    $count_sql = "SELECT COUNT(*) AS total
                                        FROM clients_archive $where_sql";
                                        //$where_sql";

                                    // $sql .= " $orderBy LIMIT $limit OFFSET $offset";

                                    $stmt = $conn->prepare($sql);
                                    $count_stmt = $conn->prepare($count_sql);

                                    $params = [];
                                    $types = "";

                                    if (!empty($searchValues)) {
                                        $types .= str_repeat("s", count($searchValues));
                                        $params = array_merge($params, $searchValues);
                                    }

                                    if (!empty($params)) {
                                        $stmt->bind_param($types, ...$params);
                                        $count_stmt->bind_param($types, ...$params);
                                    }

                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    $count_stmt->execute();
                                    $count_result = $count_stmt->get_result();
                                    $total_row = $count_result ? $count_result->fetch_assoc() : null;
                                    $total = $total_row['total'] ?? 0;
                                    
                                ?>
                            </div>
                        </div>
                        <div class="table_cont">
                            <table>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Client ID</th>
                                        <th>Client Name</th>
                                        <th>Contact #</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th style="width: 10%; text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                    $number = (($page - 1) * $limit) + 1;

                                    if($result->num_rows > 0) 
                                    {
                                        while($row = $result->fetch_assoc())
                                        {
                                            $client_id = htmlspecialchars($row['client_id']);
                                            $c_fullname = htmlspecialchars($row['fullname']);
                                            $c_contact = htmlspecialchars($row['contact']);
                                            $c_email = htmlspecialchars($row['email']);
                                            $c_address = htmlspecialchars($row['address']);
                                            $c_created_date = htmlspecialchars($row['created_at']);

                                            echo 
                                            "
                                            <tr>
                                                <td> $number </td>
                                                <td> $client_id </td>
                                                <td> $c_fullname </td>
                                                <td> $c_contact </td>
                                                <td> $c_email </td>
                                                <td> 
                                                    <div class=\"truncate\"> $c_address </div> 
                                                </td>
                                                <td style=\"width: 10%; text-align: center;\">
                                                    <a href=\"../archives/archived_client_details.php?id=" . $client_id . "\"><button style=\"background-color: transparent; border: none;\"><img src=\"../resources/img/icons/open.png\" alt=\"open\" style=\"width: 80%; opacity: 0.6; background-color: transparent; border: none;\"></button></a>
                                                </td>
                                            </tr>
                                            ";
                                            $number++;
                                        }
                                    }
                                    else
                                    {
                                        echo
                                            "
                                                <tr style='height: 43vh; border: none; cursor: auto;'>
                                                    <td rowspan='5' colspan='7' class='no_records_found'> 
                                                        <br>
                                                        <img src=\"../resources/img/icons/no_record_big.png\" alt\"no_records_found\">
                                                        <h3 style='font-size: 18px;'>No Records Found</h3>
                                                        <br>
                                                        <p style='font-size: 15px; opacity: 0.85;'>Try searching a different category or create a new data.</p>
                                                        <br>
                                                    </td>
                                                </tr>
                                            ";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="data_table_actions">
                            <div class="data_table_actions_components">
                                <p>
                                    <?php 
                                        $shown = $result->num_rows;
                                        $start = $offset + 1; 
                                        $end = $offset + $shown; 

                                        if ($total == 0 || $end == 0) {
                                            echo "Showing 0 entries";
                                        } else {
                                            echo "Showing $start – $end of $total entries";
                                        }
                                    ?>
                                </p>
                            </div>
                            <div class="data_table_actions_components">
                                <?php
                                    include("../includes/pagination.php")
                                ?>
                            </div>
                            <div class="data_table_actions_components">
                                <div class="data_actions">
                                    <button><img src="../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </section>
    </main>
    <div class="result_cont_bar">
        <?php
            //$_SESSION['archive_success_msg'] = 'Test';

            if (isset($_SESSION['period_success_msg'])) {
                echo "<span id=\"period_success\" class=\"message_success_d\"><img src=\"../resources/img/icons/check_g2.png\" alt=\"success\">" . $_SESSION['period_success_msg'] . "</span>";
    
                // 2. Add JavaScript immediately after the message to hide it after 3 seconds
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('period_success');
                        if (element) {
                            // Use CSS opacity/transition for a smooth fade out (optional)
                            element.style.transition = 'opacity 0.5s ease-out';
                            element.style.opacity = '0';

                            // Remove the element completely after the fade out is complete
                            setTimeout(function() {
                                element.style.display = 'none';
                                // Or remove it from the DOM entirely:
                                // element.parentNode.removeChild(element);
                            }, 500); // 500ms should match your CSS transition time if you add one
                        }
                    }

                    // Call the hideMessage function after 3000 milliseconds (3 seconds)
                    setTimeout(hideMessage, 5000);
                </script>
                ";

                unset($_SESSION['period_success_msg']);
            }
        ?>
    </div>
</body>
</html>