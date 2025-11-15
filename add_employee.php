<?php
include 'Database/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $bio = $_POST['bio'];
    $email = $_POST['email'];

    // Handle file uploads for profile picture and badges
    $profile_picture = $_FILES['profile_picture']['name'];

    // Move uploaded files to the "uploads" directory
    move_uploaded_file($_FILES['profile_picture']['tmp_name'], 'uploads/' . $profile_picture);

    // Get the availability (JSON string) from the request
    $availability_json = $_POST['availability'];

    // Make sure it's valid JSON
    $availability_array = json_decode($availability_json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Invalid availability data';
        exit;
    }


    try {
        $query = $pdo->prepare('INSERT INTO employees (name, role, email, profile_picture, availability, bio, isDeleted) 
            VALUES (:name, :role, :email, :profile_picture, :availability, :bio, 0)');
        $query->bindParam(':name', $name);
        $query->bindParam(':role', $role);
        $query->bindParam(':profile_picture', $profile_picture);
        $query->bindParam(':availability', $availability_json);
        $query->bindParam(':bio', $bio);
        $query->bindParam(':email', $email);
        $query->execute();

        echo 'Medewerker toegevoegd.';
    } catch (PDOException $e) {
        echo 'Fout bij het toevoegen: ' . $e->getMessage();
    }
}
?>
