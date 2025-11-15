<?php
require_once 'Database/db_connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['badge_id'])) {
    $badgeId = $_POST['badge_id'];

    // Fetch the badge image path to delete the file from the server
    $stmt = $pdo->prepare("SELECT badge_image FROM badges WHERE id = ?");
    $stmt->execute([$badgeId]);
    $badge = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($badge) {
        $badgeImagePath = $badge['badge_image'];

        // Delete the badge from the database
        $stmt = $pdo->prepare("DELETE FROM badges WHERE id = ?");
        $stmt->execute([$badgeId]);

        // Delete the badge image from the server
        if (file_exists($badgeImagePath)) {
            unlink($badgeImagePath);
        }

        $_SESSION['delete_success'] = "Badge deleted successfully!";
    } else {
        $_SESSION['delete_error'] = "Badge not found.";
    }
} else {
    $_SESSION['delete_error'] = "Invalid request.";
}

header('Location: add_badge.php');
exit();
?>