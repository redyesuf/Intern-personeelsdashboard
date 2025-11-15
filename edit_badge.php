<?php
require_once 'Database/db_connection.php';

session_start();

if (isset($_GET['id'])) {
    $badgeId = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM badges WHERE id = ?");
    $stmt->execute([$badgeId]);
    $badge = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$badge) {
        $_SESSION['edit_error'] = "Badge not found.";
        header('Location: add_badge.php');
        exit();
    }
} else {
    $_SESSION['edit_error'] = "Invalid request.";
    header('Location: add_badge.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['badge_id'])) {
    $badgeId = $_POST['badge_id'];
    $badgeName = $_POST['badge_name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("SELECT * FROM badges WHERE id = ?");
    $stmt->execute([$badgeId]);
    $badge = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($badge) {
        $badgeImage = $badge['badge_image'];

        // Handle image upload if new file was selected
        if ($_FILES['badge_image']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "uploads/";
            $targetFile = $targetDir . uniqid() . '_' . basename($_FILES["badge_image"]["name"]);
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            // Validate image
            $check = getimagesize($_FILES["badge_image"]["tmp_name"]);
            if ($check !== false) {
                if (move_uploaded_file($_FILES["badge_image"]["tmp_name"], $targetFile)) {
                    // Delete old image
                    if (file_exists($badge['badge_image'])) {
                        unlink($badge['badge_image']);
                    }
                    $badgeImage = $targetFile;
                } else {
                    $_SESSION['edit_error'] = "Error uploading image.";
                }
            } else {
                $_SESSION['edit_error'] = "File is not an image.";
            }
        }

        // Update badge information
        $stmt = $pdo->prepare("UPDATE badges SET badge_name = ?, description = ?, badge_image = ? WHERE id = ?");
        if ($stmt->execute([$badgeName, $description, $badgeImage, $badgeId])) {
            $_SESSION['edit_success'] = "Badge updated successfully!";
        } else {
            $_SESSION['edit_error'] = "Failed to update badge.";
        }
    } else {
        $_SESSION['edit_error'] = "Badge not found.";
    }

    header('Location: add_badge.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Badge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-preview {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
            border: 2px dashed #ddd;
            padding: 5px;
            border-radius: 5px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card mx-auto" style="max-width: 800px;">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Edit Badge</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="badge_id" value="<?= htmlspecialchars($_GET['id']) ?>">
                    
                    <div class="row g-4">
                        <!-- Image Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="border p-2 text-center">
                                    <img src="<?= htmlspecialchars($badge['badge_image']) ?>" 
                                         alt="Current Badge Image" 
                                         class="img-fluid rounded"
                                         style="max-height: 200px;">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="badge_image" class="form-label">Update Image (Optional)</label>
                                <input type="file" 
                                       class="form-control" 
                                       id="badge_image" 
                                       name="badge_image" 
                                       accept="image/*"
                                       onchange="previewImage(event)">
                                <div class="mt-2">
                                    <img id="imagePreview" class="image-preview" src="#" alt="Image Preview"/>
                                </div>
                                <small class="text-muted">Leave empty to keep current image</small>
                            </div>
                        </div>

                        <!-- Details Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="badge_name" class="form-label">Badge Name</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="badge_name" 
                                       name="badge_name" 
                                       value="<?= htmlspecialchars($badge['badge_name']) ?>"
                                       required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" 
                                          id="description" 
                                          name="description" 
                                          rows="4"
                                          placeholder="Enter badge description"><?= htmlspecialchars($badge['description']) ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="add_badge.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Badge</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            const preview = document.getElementById('imagePreview');
            
            reader.onload = function() {
                preview.src = reader.result;
                preview.style.display = 'block';
            }
            
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>