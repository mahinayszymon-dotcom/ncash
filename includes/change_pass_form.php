<?php
    if(isset($_POST['login']))
    {
        if (!isset($_POST['password'], $_POST['newpass'], $_POST['conpass'])) 
        {
            $_SESSION['chg_pass_error'] = "Fill out missing fields in the submission.";
            $_SESSION['open_pass_card'] = true;
        } 
        else 
        {
            $passInput = htmlspecialchars($_POST['password']); 
            $newPass = htmlspecialchars($_POST['newpass']);  
            $conPass = htmlspecialchars($_POST['conpass']); 

            $sql = "SELECT password FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0)
            {
                $row = $result->fetch_assoc();
                $password = trim($row['password']); 
            } 
            else 
            {
                $_SESSION['chg_pass_error'] = "User Password couldn't be retrieved.";
                $_SESSION['open_pass_card'] = true;
            }

            if(isset($password) && password_verify($passInput, $password))
            {
                if($newPass === $conPass)
                {
                    if(password_verify($newPass, $password)) 
                    {
                        $test_msg = "New Password matches Current Password.";
                        $_SESSION['open_pass_card'] = true;
                    }
                    else 
                    {
                        $hashedNewPass = password_hash($newPass, PASSWORD_DEFAULT);
                        $curDate = new DateTime();
                        $current = $curDate->format('Y-m-d H:i:s');

                        $sql = "UPDATE users 
                                SET password = ?, updated_at = ? 
                                WHERE user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("ssi", $hashedNewPass, $current, $user_id);
                        
                        if($stmt->execute())
                        {
                            $_SESSION = [];
                            session_unset();
                            session_destroy();

                            header("Cache-Control: no-cache, no-store, must-revalidate");
                            header("Pragma: no-cache");
                            header("Expires: 0");

                            header("Clear-Site-Data: \"cache\", \"cookies\", \"storage\", \"executionContexts\"");

                            header("Location: ../../auth/login.php");
                            exit();
                        }
                        else 
                        {
                            $_SESSION['chg_pass_error'] = "Database error occurred while updating password.";
                            $_SESSION['open_pass_card'] = true;
                        }
                    }
                }
                else
                {
                    $_SESSION['chg_pass_error'] = "New password and confirmation password does not match.";
                    $_SESSION['open_pass_card'] = true;
                }
            }
            else if (isset($password))
            {
                $_SESSION['chg_pass_error'] = "Current password entered does not match with our records.";
                $_SESSION['open_pass_card'] = true;
            }
        }
    }

    ob_end_flush();
?>
<div class="card_general_profile card_main">
    <div class="profile_text">             
        <p>Change Password</p>
        <?php  
            $sql = "SELECT DATEDIFF(CURDATE(), updated_at) AS update_days FROM users WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0)
            {
                $row = $result->fetch_assoc();
                $update_days = htmlspecialchars($row['update_days']);

                if ($update_days == 0) {
                    $state = "today";
                } else if ($update_days == 1) {
                    $state = "yesterday";
                } else {
                    $state = $update_days . " days ago";
                }
            } else {
                $state = "on record";
            }
            echo "<p class='p_settings'> Your Password was last changed $state.</p>";
        ?>
    </div>          
    <div class="profile_more">
        <button onclick="this.closest('.card_general').querySelector('.dropdown_content').classList.toggle('show'); this.classList.toggle('rotate')"><img src="../../resources/img/icons/chevron_down.png" alt="chevron_down"></button>
    </div>
</div>
<div class="dropdown_content <?php echo isset($_SESSION['open_pass_card']) ? 'show' : ''; ?>">
    <form action="" method="POST">
        <div class="change_pass_cont">
            <div class="top_content">
                <span class="message_info"><img src="../../resources/img/icons/bulb.png" alt="info">Password should be at least 12 characters long, and include a mix of uppercase and lowercase letters, numbers, and symbols.</span>
            </div>
            <div class="input_container">
                <label for="password">Current Password</label>
                <input type="password" class="password" name="password" id="password" onpaste="return false;" required>
                <!-- <img src="../../resources/img/icons/visibility_off.png" alt="password" class="visibility_icon"> -->
            </div>
            <div class="input_container">
                <label for="newpass">New Password</label>
                <input type="password" class="password" name="newpass" id="newpass" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{12,}" required>
                <!-- <img src="../../resources/img/icons/visibility_off.png" alt="password" class="visibility_icon"> -->
            </div>
            <div class="input_container">
                <label for="conpass">Re-enter New Password</label>
                <input type="password" class="password" name="conpass" id="conpass" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{12,}" required>
                <!-- <img src="../../resources/img/icons/visibility_off.png" alt="password" class="visibility_icon"> -->
            </div>                                                 
            <div class="login_btn_cont">
                <button type="submit" name="login">Change Password</button>
            </div>
            <div class="result_cont">
                <?php
                    if (isset($_SESSION['success_msg'])) {
                        echo "<span class=\"message_success\"><img src=\"../../resources/img/icons/check.png\" alt=\"success\">" . $_SESSION['success_msg'] . "</span>";
                        unset($_SESSION['success_msg']);
                        unset($_SESSION['open_pass_card']);
                    } else if (isset($_SESSION['chg_pass_error'])) {
                        echo "<span class=\"message_error\"><img src=\"../../resources/img/icons/error.png\" alt=\"error\">" . $_SESSION['chg_pass_error'] . "</span>";
                        unset($_SESSION['chg_pass_error']);
                        unset($_SESSION['open_pass_card']);
                    } else {
                        // dko alam ano lalagay dito
                    }
                ?>
            </div>
        </div>
    </form>
</div>