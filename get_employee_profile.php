<?php
include 'Database/db_connection.php';

// Get the employee ID from the URL
$employeeId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Check if employeeId is valid
if ($employeeId <= 0) {
    echo json_encode(['error' => 'Invalid employee ID']);
    exit;
}

try {
    // Query to fetch employee details
    $stmt = $pdo->prepare("
        SELECT id, name, email, role, bio, profile_picture, availability
        FROM employees 
        WHERE id = :id
    ");
    $stmt->bindParam(':id', $employeeId, PDO::PARAM_INT);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($employee) {
        // Query to fetch the employee's badges
        $badgeStmt = $pdo->prepare("
            SELECT b.badge_name, b.badge_image 
            FROM badges b
            JOIN employee_badges eb ON b.id = eb.badge_id 
            WHERE eb.employee_id = :id
        ");
        $badgeStmt->bindParam(':id', $employeeId, PDO::PARAM_INT);
        $badgeStmt->execute();
        $badges = $badgeStmt->fetchAll(PDO::FETCH_ASSOC);

        // Attach the badges to the employee data
        $employee['badges'] = $badges;

        echo json_encode($employee);
    } else {
        echo json_encode(['error' => 'Employee not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>
