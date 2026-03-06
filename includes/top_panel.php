<div class="top_panel">
    <div class="top_panel_content">
        <div class="text_cont">
            <h1><?php echo (basename($_SERVER['PHP_SELF'], ".php") === "home") ? "Dashboard" : ucwords(str_replace("_", " ",basename($_SERVER['PHP_SELF'], ".php")));?></h1>
        </div>
        <div class="search_cont">
            <input type="text" placeholder="<?php echo "What would you like to search this " . date('l') . "?";?>">
            <img src="../resources/img/icons/search.png" alt="search">
        </div>
        <div class="account_cont">       
            <div class="profile_circle">
                <?php
                    echo "<p>" . mb_substr($fullname, 0, 1). "</p>";
                ?>
            </div>
            <div class="profile_text">
                <?php
                    echo "<a href=\"../dashboard/settings.php\"><p>" . ucwords($fullname) . "</p></a>";
                    echo "<p>" . ucwords($branch_name) . " (" . ucwords($role) . ")</p>";
                ?>
            </div>
            <div class="account_cont_actions"> 
                <button onclick="window.location.href='../dashboard/notifications.php';"><img src="../resources/img/icons/notif.png" alt="notifications"></button>   
                <form id="logout-form" action="../auth/logout.php" method="POST">
                    <button type="submit"><img src="../resources/img/icons/logout.png" alt="logout"></button>
                </form>
            </div>
        </div>
    </div>
</div>