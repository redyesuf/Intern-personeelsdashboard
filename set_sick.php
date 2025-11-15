<?php
include 'Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $location = $_POST['location'];

    try {
        $query = $pdo->prepare('UPDATE employees SET location = :location WHERE id = :id');
        $query->bindParam(':location', $location, PDO::PARAM_STR);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        $query = $pdo->prepare('UPDATE employees SET status = "absent" WHERE id = :id');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();

        echo 'Locatie bijgewerkt.';
    } catch (PDOException $e) {
        echo 'Fout bij het bijwerken: ' . $e->getMessage();
    }
}
?>