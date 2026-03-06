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
    <title>Audit End Balance</title>
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
                        <h1>End Balance</h1>
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
                                <img src="../../resources/img/icons/end_bal.png" alt="end_bal">
                            </div>
                            <h2>Audit End Balance</h2>
                        </div>
                        <div class="data_controls_header_button">
                            <button onclick="window.location.href='../transactions.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
                        </div>
                    </div>
                    
                    <div class="end_bal_cont">
                        <div class="end_bal">
                            <div class="end_bal_amount">
                                <div class="eb_title">
                                    <p>END BALANCE</p>
                                </div>
                                <div class="eb_value_cont">
                                    <div class="eb_value">
                                        <p>₱ 0.00</p>
                                    </div>
                                    <div class="eb_action">
                                        <form action="" method="POST">
                                            <?php
                                                if($role === "admin")
                                                    {
                                                        echo '<div class="form_conts">
                                                            <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                            <select name="branch" id="branch" onchange="this.form.submit()" class="sort2">
                                                                
                                                                <option value="pasig" ' . '>Pasig Branch</option>
                                                                
                                                                <option value="quezon" ' . '>Quezon City Branch</option>
                                                                
                                                                <option value="makati" ' . '>Makati City Branch</option>
                                                                
                                                            </select>
                                                            <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                                        </div>';
                                                    }
                                            ?>
                                            <div class="form_conts">
                                                <button><img src="../../resources/img/icons/edit_w_s.png" alt="edit"></button>
                                            </div>
                                        </form>              
                                    </div>
                                </div>
                            </div>
                            <div class="quick_transac">
                                <div class="eb_title">
                                    <p>QUICK TRANSACTION</p>
                                </div>
                                <div class="eb_transac_cont">
                                    <div class="eb_input">
                                        <form action="" method="POST">
                                            <div class="form_conts">
                                                <input type="text" placeholder="Name your transaction">
                                            </div>
                                            <div class="form_conts">
                                                <input type="number" placeholder="Amount">
                                            </div>
                                            <div class="form_conts">
                                                <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                <select name="type" id="type" onchange="this.form.submit()" class="sort3">
                                                    <option value="debit">Debit</option>
                                                    <option value="credit">Credit</option>   
                                                </select>
                                                <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                            </div>
                                            <?php
                                                if($role === "admin")
                                                    {
                                                        echo '<div class="form_conts">
                                                            <span class="custom-arrow-sort2"><img src="../../resources/img/icons/filter_w.png" alt="filter"></span>
                                                            <select name="branch" id="branch" onchange="this.form.submit()" class="sort2">
                                                                
                                                                <option value="pasig" ' . '>Pasig Branch</option>
                                                                
                                                                <option value="quezon" ' . '>Quezon City Branch</option>
                                                                
                                                                <option value="makati" ' . '>Makati City Branch</option>
                                                                
                                                            </select>
                                                            <span class="custom-arrow2"><img src="../../resources/img/icons/branch_down.png" alt="sort"></span>
                                                        </div>';
                                                    }
                                            ?>
                                            <div class="form_conts">
                                                <button><img src="../../resources/img/icons/add_w_s.png" alt="add"></button>
                                            </div>
                                        </form>              
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="end_bal_table">
                            <div class="table_cont">
                                <table id="audit">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            /*Count*/
                                            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                                            $limit = 12;
                                            $offset = ($page - 1) * $limit;
    
                                            $total = $total_row['total'] ?? 0;
                                        ?>
                                        <!--No record-->
                                        <tr>
                                            <td colspan="4" style="text-align: center; padding: 1rem 0;">No End Balance Transactions Recorded Yet</td>
                                        </tr>
                                        <!--has record-->
                                        <tr>
                                            <td>--</td>
                                            <td>--</td>
                                            <td>--</td>
                                            <td><button><img src="../../resources/img/icons/delete2.png" alt="delete"></button></td>
                                        </tr>
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
                                    <div class="pagination">
                                        <?php
                                            $total_pages = ceil($total / $limit);

                                            // Previous link
                                            if ($page > 1) {
                                                $prev = $page - 1;
                                                echo "<div class=\"page_button_direct\"><a href='?page=$prev&branch=$sorting'><</a></div>";
                                            }

                                            // Page number links
                                            for ($i = 1; $i <= $total_pages; $i++) {
                                                if ($i == $page) {
                                                    echo "<div class=\"page_button_active\">$i</div>"; // current page highlighted
                                                } else {
                                                    echo "<div class=\"page_button\"><a href='?page=$i&branch=$sorting'>$i</a></div>";
                                                }
                                            }

                                            // Next link
                                            if ($page < $total_pages) {
                                                $next = $page + 1;
                                                echo "<div class=\"page_button_direct\"><a href='?page=$next&branch=$sorting'>></a></div>";
                                            }
                                        ?>
                                    </div>
                                </div>
                                <div class="data_table_actions_components">
                                    <div class="data_actions">          
                                        <!--Supposedly dapat naka disable eto. kapag nag check ako sa ilang checkbox, tsaka lang sya mag enable. Dito na ren si multiple selection-->
                                        <!-- <button><img src="../resources/img/icons/edit.png" alt="edit"><p>Edit</p></button>
                                        <button><img src="../resources/img/icons/archive.png" alt="archive"><p>Archive</p></button> -->
                                        <button onclick="window.location.href='balance_audit.php'"><img src="../../resources/img/icons/refresh.png" alt="refresh"><p>Refresh</p></button>
                                        <!-- <button><img src="../resources/img/icons/open.png" alt="view"><p>View</p></button> -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>