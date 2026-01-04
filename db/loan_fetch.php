<?php
    include("../config/db_conn.php");

    header('Content-Type: application/json');
    
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]);
        exit();
    }

    if (isset($_POST['agreement_num'])) {
        $item_id = $_POST['agreement_num'];
        
        $sql = "SELECT c.fullname, i.item_name, i.principal, i.interest 
                FROM inventory AS i 
                INNER JOIN clients AS c ON i.client_id = c.client_id 
                WHERE item_id = ?";
        $stmt = $conn->prepare($sql);

        $stmt->bind_param("i", $item_id);

        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'fullname' => $row['fullname'],
                'item_name' => $row['item_name'],
                'principal' => $row['principal'],
                'interest' => $row['interest']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No details found for this agreement number in the database.'
            ]);
        }

        $stmt->close();
    } 
    else 
    {
        echo json_encode([
            'success' => false,
            'message' => 'Agreement number not provided in the request.'
        ]);
    }

    $conn->close();
?>