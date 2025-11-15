<?php
include 'Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];
    $location = $_POST['location'];
    $availability = $_POST['availability'];  // Get updated availability

    try {
        $query = $pdo->prepare('UPDATE employees SET status = :status, location = :location, availability = :availability WHERE id = :id');
        $query->bindParam(':status', $status);
        $query->bindParam(':location', $location);
        $query->bindParam(':availability', $availability);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo 'Status and availability updated successfully.';
    } catch (PDOException $e) {
        echo 'Error updating: ' . $e->getMessage();
    }
}
?>
