<?php
    include("../config/db_conn.php");

    header('Content-Type: application/json');

    $branch_id = (int)$_POST['branch_id'];
    $next_agreement = 1;

    $sql = "SELECT MAX(agreement_num) AS max_agreement FROM inventory WHERE branch_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $branch_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) 
    {
        $select_row = $result->fetch_assoc();
        $max_num = ($select_row['max_agreement'] ?? 0);

        $sql = "SELECT MAX(agreement_num) AS max_archive FROM items_archive WHERE branch_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $branch_id);
        $stmt->execute();
        $fetch_result = $stmt->get_result();

        if($fetch_result->num_rows > 0) 
        {
            $fetch_row = $fetch_result->fetch_assoc();
            $a_agreement = ($fetch_row['max_archive'] ?? 0);
        }

        if($max_num > $a_agreement)
        {
            $next_agreement = $max_num + 1;
        }
        else 
        {
            $next_agreement = $a_agreement + 1;
        }
    }

    $stmt->close();

    echo json_encode(['next_agreement' => $next_agreement]);
?>