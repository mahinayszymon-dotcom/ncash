<?php
include("../config/session_check.php");
include("../config/db_conn.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="icon" type="image/png" href="../resources/img/favicon.png">
    <link rel="stylesheet" href="../resources/css/base.css">
    <link rel="stylesheet" href="../resources/css/colors.css">
    <link rel="stylesheet" href="../resources/css/fonts.css">
    <link rel="stylesheet" href="../resources/css/pages/dashboard/reports.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
</head>
<body>
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
            <div class="central_panelD">
                <div class="hero_panelB">
                    <img src="../resources/img/graphics/reports_graphic5.png" class="graphic" alt="reports_graphic">
                    <div class="hero_container">
                        <div class="hero_header">
                            <h1>Generate Branch Reports</h1>
                        </div>
                        <div class="hero_form">
                            <form action="" style="<?php if ($role !== "admin") { echo "justify-content: start !important; gap: 15px;";} ?>">
                                <div class="form_conts">
                                    <label for="begin_date">Begin Date</label>
                                    <input type="date" id="begin_date" name="begin_date" value="<?php echo isset($_GET['begin_date']) ? $_GET['begin_date'] : date('Y-m-d', strtotime('monday this week')); ?>">
                                </div>
                                <div class="form_conts">
                                    <label for="end_date">End Date</label>
                                    <input type="date" id="end_date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); ?>">
                                </div>
                                <?php
                                    $selected = $_GET['branch'] ?? '1100'; 
    
                                    if ($role === "admin") {
                                        echo '<div class="form_conts">
                                            <label for="branch">Branch</label>
                                            <span class="custom-arrow-sort"><img src="../resources/img/icons/filter_w.png" alt="filter"></span>
                                            <select name="branch" id="branch" onchange="this.form.submit()" class="sort">
                                                
                                                <option value="1100" ' . ($selected === '1100' ? 'selected' : '') . '>Pasig Branch</option>
                                                
                                                <option value="1101" ' . ($selected === '1101' ? 'selected' : '') . '>Quezon City Branch</option>
                                                
                                                <option value="1102" ' . ($selected === '1102' ? 'selected' : '') . '>Makati City Branch</option>
                                                
                                            </select>
                                            <span class="custom-arrow"><img src="../resources/img/icons/branch_down.png" alt="sort"></span>
                                        </div>';

                                        $staff_br_id = $selected;
                                    }
                                    else 
                                    {
                                        $staff_br_id = $_SESSION['branch_id'];
                                    }

                                    $br_sql = "SELECT branch_name FROM branches WHERE branch_id = ?";
                                    $br_stmt = $conn->prepare($br_sql);
                                    $br_stmt->bind_param("i", $staff_br_id);
                                    $br_stmt->execute();
                                    $br_res = $br_stmt->get_result();

                                    if($br_res->num_rows > 0)
                                    {
                                        $br_row = $br_res->fetch_assoc();
                                        $staff_br_name = htmlspecialchars($br_row['branch_name']);
                                    }
                                ?>   
                                <div class="form_conts">
                                    <label for="generate" style="opacity: 0;">Action</label>
                                    <button type="button" id="generate" data-branch-name="<?php echo $staff_br_name; ?>" data-branch-id="<?php echo $staff_br_id; ?>" onclick="prepReport()"><img src="../resources/img/icons/pdf.png" alt="pdf">Generate</button>
                                </div>
                            </form>
                        </div>
                        <div class="hero_footer">
                            <img src="../resources/img/icons/info_report.png" alt="info">
                            <p>Generated reports contain sensitive financial data. Please ensure you handle exported files carefully.</p>
                        </div>
                    </div>
                </div>
                <div class="data_tableB">
                    <div class="data_panel_header">
                        <div class="data_panel_name">
                            <div class="icon_normal">
                                <img src="../resources/img/icons/table_w.png" alt="table_icon">
                            </div>
                            <?php
                                if ($role === "admin") {
                                    echo '<h2>All Branch Analytics (Monthly)</h2>'; 
                                } else {
                                    if ($branch === 1100 || $branch === 1101 || $branch === 1102) {
                                        echo '<h2>Report Analytics Data (Weekly)</h2>';
                                    } else {
                                        $_SESSION['report_error_msg'] = 'Error: Cannot retrieve branch ID.';
                                        $style = 'style="display: none;"';
                                    } 
                                }
                            ?>      
                        </div>
                    </div>  
                    <div class="data_main_analytics" <?php if (isset($style)) { echo $style; }?>>
                        <?php
                            if ($role === "admin") {
                                include("../includes/reports_admin.php");
                            } else if ($role === "user") {
                                include("../includes/reports_user.php");
                            } else {
                                $_SESSION['report_error_msg'] = 'An error occured during retrieving branch analytics.';
                            }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </section>
    <div class="result_cont_bar">
        <?php
            // $_SESSION['report_error_msg'] = 'Test';

            if (isset($_SESSION['report_error_msg'])) {
                echo "<span id=\"report_error\" class=\"message_success_d\"><img src=\"../resources/img/icons/error_bright.png\" alt=\"error\">" . $_SESSION['report_error_msg'] . "</span>";
    
                echo "
                <script>
                    // Function to hide the element
                    function hideMessage() {
                        var element = document.getElementById('report_error');
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

                unset($_SESSION['report_error_msg']);
            }
        ?>
    </div>
</body>
<div id="reportModal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalBranchName">Branch Report</h2>
            <button class="close_button" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="modalDataContainer">
                <p>Loading report data...</p>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="window.print()" class="print_button"><img src="../resources/img/icons/print.png" alt="print">Print Report</button>
        </div>
    </div>
</div>
</html>
<script src="../resources/js/report_download.js"></script>