<div class="pagination">
<?php
    $total_pages = ceil($total / $limit);

    // Previous link
    if ($page > 1) {
        $prev = $page - 1;
        echo "<div class=\"page_button_direct\"><a href='?page=$prev&branch=$sorting'>&lt;</a></div>";
    }

    // --- NEW SLIDING WINDOW LOGIC ---
    if ($total_pages <= 5) {
        // If 5 or fewer pages, show them all
        $start_page = 1;
        $end_page = $total_pages;
    } else {
        // If more than 5 pages, lessen out the window
        if ($page <= 3) {
            // malapit si user sa unahan (e.g., Pages 1, 2, or 3)
            $start_page = 1;
            $end_page = 4;
            $show_last = true;
            $show_first = false;
        } elseif ($page >= $total_pages - 2) {
            // malapit si user sa dulo (e.g., last 3 pages)
            $start_page = $total_pages - 3;
            $end_page = $total_pages;
            $show_first = true;
            $show_last = false;
        } else {
            // nasa gitna si user
            $start_page = $page - 1;
            $end_page = $page + 1;
            $show_first = true;
            $show_last = true;
        }
    }

    // display first page and ellipsis if needed
    if (isset($show_first) && $show_first) {
        echo "<div class=\"page_button\"><a href='?page=1&branch=$sorting'>1</a></div>";
        echo "<div class=\"page_button_ellipsis\" style=\"opacity: 0.6;\">...</div>"; 
    }

    // loop through the calculated sliding window range
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            echo "<div class=\"page_button_active\">$i</div>"; // current page highlighted
        } else {
            echo "<div class=\"page_button\"><a href='?page=$i&branch=$sorting'>$i</a></div>";
        }
    }

    // display ellipsis and last page if needed
    if (isset($show_last) && $show_last) {
        echo "<div class=\"page_button_ellipsis\" style=\"opacity: 0.6;\">...</div>";
        echo "<div class=\"page_button\"><a href='?page=$total_pages&branch=$sorting'>$total_pages</a></div>";
    }
    // --- END SLIDING WINDOW LOGIC ---

    // next link
    if ($page < $total_pages) {
        $next = $page + 1;
        echo "<div class=\"page_button_direct\"><a href='?page=$next&branch=$sorting'>&gt;</a></div>";
    }
?>
</div>