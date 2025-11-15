<?php
// Database connection
include 'Database/db_connection.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    try {
        $isDeleted = 0;
        $query = $pdo->prepare('UPDATE employees SET isDeleted = :isDeleted WHERE id = :id');
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':isDeleted', $isDeleted, PDO::PARAM_INT);
        $query->execute();

        echo 'Medewerker succesvol verwijderd.';
    } catch (PDOException $e) {
        echo 'Fout bij het verwijderen: ' . $e->getMessage();
    }
} else {
    echo 'Ongeldige aanvraag.';
}
?>
