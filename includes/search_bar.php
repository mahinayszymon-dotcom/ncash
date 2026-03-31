<div class="search_cont">
    <form action="" method="GET">
        <input type="text" name="search" placeholder="Search" 
               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <img src="../resources/img/icons/search.png" alt="search" onclick="this.closest('form').submit();" style="cursor:pointer;">
    </form>
</div>