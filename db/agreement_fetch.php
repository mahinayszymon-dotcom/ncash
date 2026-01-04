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
        $row = $result->fetch_assoc();
        $next_agreement = ($row['max_agreement'] ?? 0) + 1;
    }

    $stmt->close();

    echo json_encode(['next_agreement' => $next_agreement]);
?>