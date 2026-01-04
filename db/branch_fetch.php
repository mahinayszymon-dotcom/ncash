<?php
    $branch_id = $_SESSION['branch_id'];

    $sql = "SELECT branch_name FROM branches WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $user_branch = $row['branch_name'];
?>