<?php
include 'Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    try {
        // Delete related badges first
        $query = $pdo->prepare('DELETE FROM employee_badges WHERE employee_id = :id');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        // Now delete the employee
        $query = $pdo->prepare('DELETE FROM employees WHERE id = :id');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo 'Medewerker verwijderd.';
    } catch (PDOException $e) {
        echo 'Fout bij het verwijderen: ' . $e->getMessage();
    }
}
?>
