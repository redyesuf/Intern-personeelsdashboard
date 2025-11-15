<?php
// get_attendance_days.php
require 'Database/db_connection.php'; 

if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT date FROM attendance_records WHERE employee_id = ? AND status = 'present' ORDER BY date");
    $stmt->execute([$employeeId]);
    $attendanceDays = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($attendanceDays);
} else {
    echo json_encode(['error' => 'Geen ID opgegeven']);
}
?>
