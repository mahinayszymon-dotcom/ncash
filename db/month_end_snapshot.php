<?php
// month_end_snapshot.php

$lock_file = __DIR__ . '/snapshot_lock.txt'; 
$current_month = date('Y-m'); 

$fp = fopen($lock_file, 'c+');

if ($fp) {
    if (flock($fp, LOCK_EX)) {
        
        $last_run_month = trim(stream_get_contents($fp));

        if ($current_month !== $last_run_month) {
            
            $update_sql = "
                UPDATE branches b
                SET 
                    prev_active_count = (SELECT COUNT(*) FROM inventory i WHERE i.branch_id = b.branch_id AND i.status = 'Active'),
                    
                    prev_redeem_count = (SELECT COUNT(*) FROM inventory i WHERE i.branch_id = b.branch_id AND i.status = 'Redeemed' 
                                         AND i.updated_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') 
                                         AND i.updated_at < DATE_FORMAT(CURDATE(), '%Y-%m-01')),
                                         
                    prev_overdue_count = (SELECT COUNT(*) FROM inventory i WHERE i.branch_id = b.branch_id AND i.status = 'Overdue'),
                    
                    prev_principal = (SELECT COALESCE(SUM(principal), 0) FROM inventory i WHERE i.branch_id = b.branch_id AND i.status != 'Redeemed')
            ";

            // ok 
            if ($conn->query($update_sql) === TRUE) {
                ftruncate($fp, 0);      // erase the old month
                rewind($fp);            // go back to the start of the file
                fwrite($fp, $current_month); // new month
            }
        }
        
        // di muna ilock so release
        flock($fp, LOCK_UN);
    }
    
    // isara
    fclose($fp);
}
?>