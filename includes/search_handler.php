<?php
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchValues = [];

if (!empty($searchTerm) && !empty($searchColumns)) {

    $conditions = [];
    $lowerSearch = strtolower($searchTerm);

    $numericSearch = preg_replace('/[^0-9]/', '', $searchTerm);

    foreach ($searchColumns as $column) {

        if ($column === 'i.agreement_num') {

            if (!empty($numericSearch)) {
                $conditions[] = "$column LIKE ?";
                $searchValues[] = "%$numericSearch%";
            }

        } else {
            $conditions[] = "LOWER($column) LIKE ?";
            $searchValues[] = "%$lowerSearch%";
        }
    }

    if (!empty($conditions)) {
        $where[] = "(" . implode(" OR ", $conditions) . ")";
    }
}
?>