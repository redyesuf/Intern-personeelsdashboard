<?php
include 'Database/db_connection.php';

try {
    $query = $pdo->query('SELECT * FROM employees');
    $employees = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($employees);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
