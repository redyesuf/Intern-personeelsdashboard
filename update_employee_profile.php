<?php
require_once 'Database/db_connection.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $role = $_POST['role'];
    $email = $_POST['email'];
    $bio = $_POST['bio'];
    $availability = $_POST['availability'];
    $status = $_POST['status'];
    $location = $_POST['location'];

    $sql = "UPDATE employees SET 
            name = ?, 
            role = ?,
            email = ?,
            bio = ?,
            status = ?,
            location = ?,
            availability = ?
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $role, $email, $bio, $status, $location, $availability, $id]);


    if ($stmt->execute()) {
        // Handle profile picture upload if provided
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
            $target_dir = "uploads/profile_pictures/";
            $file_extension = pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION);
            $file_name = "profile_" . $id . "." . $file_extension;
            $target_file = $target_dir . $file_name;

            if ($stmt->execute()) {
                if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                    $sql = "UPDATE employees SET profile_picture = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$file_name, $id]);
                    $response['profile_picture'] = $file_name; // Send filename in response
                }
            }
            
        }
        $response['success'] = true;
    }
}

echo json_encode($response);
?>