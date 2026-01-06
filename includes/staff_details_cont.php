<div class="central_panelF">
    <div class="data_controlsC">
        <div class="data_controls_header">
            <div class="data_controls_header_text">
                <div class="icon_normal">
                    <img src="../../resources/img/icons/description_w.png" alt="description">
                </div>
                <?php 
                    echo "<h2>$u_fullname's Information</h2>";
                ?>
            </div>
            <div class="data_controls_header_button">
                <button onclick="window.location.href='../staff_management.php'"><img src="../../resources/img/icons/arrow_circle_left.png" alt="return">Return</button>
            </div>
        </div>
        <div class="data_details_cont">
            <div class="details_info">
                <div class="row_cont">
                    <?php
                        $status_style = "font-size: 15px;";
                        
                        if ($u_status == 'active') {
                            $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400; padding: 5px 8px; border-radius: 5px; background-color: #d2e8ce; color: #739667;";
                        } 
                        else {
                            $status_style .= "display: inline-block; text-align: center; font-size: 15px; width: 100%; font-weight: 400;padding: 5px 8px; border-radius: 5px; background-color: #f1eceb; color: #a6a094;";
                        }

                        echo '<span style="' . $status_style . '">This user is ' . $u_status . '</span>';
                    ?>
                </div>
                <div class="creator_cont">
                    <div class="row_cont">
                        <span>Created at: <?php echo $formatted_date ?> </span>
                    </div>
                </div>
                <hr>
                <?php
                    if ($u_status === 'active') {
                        echo '<div class="disable_btn_cont">
                                <button type="submit" name="submit"><img src="../../resources/img/icons/disable.png" alt="disable">Disable User</button>
                                <div class="disable_text">
                                    <span class="message_info"><img src="../../resources/img/icons/info.png" alt="info">Disabling this user will restrict them from accessing the system.</span>
                                </div>
                            </div>';
                    } else {
                        echo '<div class="enable_btn_cont">
                                <button type="submit" name="submit" class="enable"><img src="../../resources/img/icons/enable.png" alt="enable">Enable User</button>
                                <div class="enable_text">
                                    <span class="message_info"><img src="../../resources/img/icons/info.png" alt="info">Enabling this user will give them access in the system.</span>
                                </div>
                            </div>';
                    }
                ?>
            </div>
                
            <div class="details_editable">
                <?php
                    echo '
                        <form action="" method="POST" class="editable_item_section">
                            <div class="item_info_detail_row">
                                <label for="emp_uname">Username</label>
                                <input type="text" name="emp_uname" id="emp_uname" class="item_tags" value="' . $u_uname . '" readonly disabled>
                            </div>

                            <div class="item_info_detail_row">
                                <label for="emp_name">Full Name</label>
                                <input type="text" name="emp_name" id="emp_name" class="item_tags" value="' . $u_fullname . '" disabled>
                            </div>

                            <div class="item_info_detail_row">
                                <label for="emp_email">Email Address</label>
                                <input type="email" name="emp_email" id="emp_email" class="item_tags" value="' . $u_email . '" disabled>
                            </div>';

                            echo '
                            <div class="item_info_detail_row">
                                <label for="role_select">Role</label>
                                <select name="role_select" id="role_select" class="item_tags" disabled>
                                    <option value="admin" ' . (($u_role == "admin") ? "selected" : "") . '>Administrator</option>
                                    <option value="user" ' . (($u_role == "user") ? "selected" : "") . '>User</option>
                                </select>
                            </div>';

                            echo '
                            <div class="item_info_detail_row">
                                <label for="ubranch_select">Branch</label>
                                <select name="ubranch_select" id="ubranch_select" class="item_tags" disabled>
                                    <option value="mp" ' . (($u_branch == "1100") ? "selected" : "") . '>Marikina-Pasig</option>
                                    <option value="qc" ' . (($u_branch == "1101") ? "selected" : "") . '>Quezon City</option>
                                    <option value="mc" ' . (($u_branch == "1102") ? "selected" : "") . '>Makati City</option>
                                </select>
                            </div>';
                            

                        echo '
                            <div class="item_info_detail_btn">
                                <button type="button" id="editBtn">Edit</button>
                                <button type="submit" id="submit" name="submit" disabled>Save Changes</button>
                            </div>
                        </form>';
                ?>
                <div class="result_cont">
                    <?php
                        // $_SESSION['change_success_msg'] = '';

                        if (isset($_SESSION['change_success_msg'])) {
                            $redirect_url = "../../dashboard/inventory.php";
                            $delay = 3; // three seconds muna taymperst

                            echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['change_success_msg'] . "</span>";
                            
                            echo "<meta http-equiv='refresh' content='" . $delay . "; url=" . $redirect_url . "'>";

                            unset($_SESSION['change_success_msg']); 
                        } else if (isset($_SESSION['error_msg'])) {
                            echo "<span class=\"message_error\"><img src=\"../../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['error_msg'] . "</span>";
                            unset($_SESSION['error_msg']);
                        } else if (isset($_SESSION['nochange_msg'])) {
                            echo "<span class=\"message_info\"><img src=\"../../resources/img/icons/info.png\" alt=\"info\">" . $_SESSION['nochange_msg'] . "</span>";
                            unset($_SESSION['nochange_msg']);
                        } else {
                            unset($_SESSION['change_success_msg']);
                            unset($_SESSION['nochange_msg']);
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>