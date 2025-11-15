<?php
require_once 'Database/db_connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $badgeName = $pdo->quote($_POST['badge_name']);

    $targetDir = "uploads/badges/";
    $targetFile = $targetDir . basename($_FILES["badge_image"]["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Move uploaded file to the uploads directory
    if (move_uploaded_file($_FILES["badge_image"]["tmp_name"], $targetFile)) {
        $stmt = $pdo->prepare("INSERT INTO badges (badge_name, description,badge_image) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['badge_name'], $_POST['description'], $targetFile]);

        $_SESSION['uploaded_successfully'] = "Badge uploaded successfully!";
    } else {
        $_SESSION['error_uploading'] = "Sorry, there was an error uploading your file.";
    }
}

// Fetch all badges
$stmt = $pdo->query("SELECT * FROM badges");
$badges = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Badge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .badge-preview {
            max-width: 150px;
            height: auto;
            border-radius: 8px;
            transition: transform 0.3s ease;
        }
        .badge-preview:hover {
            transform: scale(1.05);
        }
        .description-cell {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Manage Badges</h2>
                <a href="index.html" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            <div id="message_area">
                <?php
                    if (isset($_SESSION['uploaded_successfully'])) {
                        echo "<div class='alert alert-success'>".$_SESSION['uploaded_successfully']."</div>";
                        unset($_SESSION['uploaded_successfully']);
                    }

                    if (isset($_SESSION['error_uploading'])) {
                        echo "<div class='alert alert-danger'>".$_SESSION['error_uploading'] ."</div>";
                        unset($_SESSION['error_uploading']);
                    }

                    if (isset($_SESSION['delete_success'])) {
                        echo "<div class='alert alert-success'>".$_SESSION['delete_success']."</div>";
                        unset($_SESSION['delete_success']);
                    }

                    if (isset($_SESSION['delete_error'])) {
                        echo "<div class='alert alert-danger'>".$_SESSION['delete_error'] ."</div>";
                        unset($_SESSION['delete_error']);
                    }

                    if (isset($_SESSION['edit_success'])) {
                        echo "<div class='alert alert-success'>".$_SESSION['edit_success']."</div>";
                        unset($_SESSION['edit_success']);
                    }

                    if (isset($_SESSION['edit_error'])) {
                        echo "<div class='alert alert-danger'>".$_SESSION['edit_error'] ."</div>";
                        unset($_SESSION['edit_error']);
                    }
                ?>
            </div>
            <!-- Badge Form -->
            <form action="" method="POST" enctype="multipart/form-data" class="mb-5">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="badge_name" class="form-label">Badge Name</label>
                        <input type="text" class="form-control form-control-lg" 
                               id="badge_name" name="badge_name" required>
                    </div>

                    <div class="col-md-6">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control form-control-lg" 
                                  id="description" name="description" 
                                  rows="2" required></textarea>
                    </div>

                    <div class="col-12">
                        <label for="badge_image" class="form-label">Badge Image</label>
                        <input type="file" class="form-control form-control-lg" 
                               id="badge_image" name="badge_image" 
                               accept="image/*" required>
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-plus-circle me-2"></i>Add Badge
                        </button>
                    </div>
                </div>
            </form>

            <!-- Existing Badges Table -->
            <h3 class="mt-5 mb-4">Existing Badges</h3>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Preview</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($badges as $badge): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($badge['badge_image']) ?>" 
                                     class="badge-preview" 
                                     alt="<?= htmlspecialchars($badge['badge_name']) ?>">
                            </td>
                            <td class="fw-bold"><?= htmlspecialchars($badge['badge_name']) ?></td>
                            <td class="description-cell" title="<?= htmlspecialchars($badge['description']) ?>">
                                <?= htmlspecialchars($badge['description']) ?>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_badge.php?id=<?= $badge['id'] ?>" 
                                       class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="delete_badge.php" method="POST">
                                        <input type="hidden" name="badge_id" value="<?= $badge['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Message timeout
        setTimeout(() => {
            const messageArea = document.getElementById('message_area');
            if (messageArea) {
                messageArea.style.opacity = '0';
                setTimeout(() => {
                    messageArea.style.display = 'none';
                }, 500);
            }
        }, 5000);

        
        document.addEventListener('DOMContentLoaded', function() {
            const deleteForms = document.querySelectorAll('form[action="delete_badge.php"]');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Are you sure you want to delete this badge?')) {
                        e.preventDefault(); // Stop form submission if canceled
                    }
                });
            });
        });

        // Initialize tooltips and hover effects
        document.addEventListener('DOMContentLoaded', () => {
            // Bootstrap tooltips
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(el => new bootstrap.Tooltip(el));

            // Dynamic tooltip for description cells
            document.querySelectorAll('.description-cell').forEach(cell => {
                if (cell.offsetWidth < cell.scrollWidth) {
                    cell.setAttribute('data-bs-toggle', 'tooltip');
                    cell.setAttribute('title', cell.textContent);
                }
            });
        });

    </script>
    
</body>
</html>