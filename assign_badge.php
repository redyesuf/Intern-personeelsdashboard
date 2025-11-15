<?php
require 'Database/db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_ids = $_POST['employee_id'] ?? [];
    $badge_ids = $_POST['badge_id'] ?? [];

    if (empty($employee_ids) || empty($badge_ids)) {
        $_SESSION['please_select_both'] = "Please select at least one employee and one badge.";
        header("Location: assign_badge.php");
        exit;
    }

    // Initialize counters
    $assigned = 0;
    $duplicates = 0;

    // Prepare statements
    $checkStmt = $pdo->prepare("SELECT * FROM employee_badges WHERE employee_id = ? AND badge_id = ?");
    $insertStmt = $pdo->prepare("INSERT INTO employee_badges (employee_id, badge_id) VALUES (?, ?)");
    $badgeStmt = $pdo->prepare("SELECT badge_name FROM badges WHERE id = ?");
    $updateStmt = $pdo->prepare("UPDATE employees SET badges = CONCAT(IFNULL(badges, ''), ?, ',') WHERE id = ?");

    foreach ($employee_ids as $employee_id) {
        foreach ($badge_ids as $badge_id) {
            // Check for existing assignment
            $checkStmt->execute([$employee_id, $badge_id]);
            if ($checkStmt->rowCount() === 0) {
                // Insert new assignment
                if ($insertStmt->execute([$employee_id, $badge_id])) {
                    $assigned++;

                    // Update badges column
                    $badgeStmt->execute([$badge_id]);
                    if ($badge = $badgeStmt->fetch()) {
                        $updateStmt->execute([$badge['badge_name'], $employee_id]);
                    }
                }
            } else {
                $duplicates++;
            }
        }
    }

    // Set session messages
    if ($assigned > 0) {
        $_SESSION['assigned_successfully'] = "Successfully assigned $assigned badge(s)!";
    }
    if ($duplicates > 0) {
        $_SESSION['already_assigned'] = "$duplicates duplicate assignments were skipped.";
    }

    header("Location: assign_badge.php");
    exit;
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Badge to Employee</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <style>
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .select-title {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }
        .bootstrap-select .btn {
            border: 1px solid #ced4da;
            padding: 0.75rem;
        }
        .badge-counter {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.9em;
        }
        .select-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .help-text {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <div class="select-header">
                <h1 class="h3 mb-0">Assign Badges</h1>
                <a href="index.html" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
            
            <div id="message_area">
                <?php  if (isset($_SESSION['please_select_both'])) {
                            echo "<div class='alert alert-danger'>".$_SESSION['please_select_both']."</div>";
                            unset($_SESSION['please_select_both']);
                        }

                        if (isset($_SESSION['assigned_successfully'])) {
                            echo "<div class='alert alert-success'>".$_SESSION['assigned_successfully'] ."</div>";
                            unset($_SESSION['assigned_successfully']);
                        }

                        if (isset($_SESSION['already_assigned'])) {
                            echo "<div class='alert alert-success'>".$_SESSION['already_assigned']."</div>";
                            unset($_SESSION['already_assigned']);
                        }
                        ?>
            </div>

            <form action="" method="POST">
                <!-- Employees Select -->
                <div class="mb-4">
                    <label class="select-title">Select Employees</label>
                    <select name="employee_id[]" class="selectpicker w-100" multiple data-live-search="true" data-style="bg-white" data-size="5" title="Choose employees...">
                    <?php
                    $stmt = $pdo->query("SELECT id, name, role FROM employees");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<option value="' 
                            . htmlspecialchars($row['id'], ENT_QUOTES) 
                            . '" data-subtext="' 
                            . htmlspecialchars($row['role'], ENT_QUOTES) 
                            . '">' 
                            . htmlspecialchars($row['name'], ENT_QUOTES) 
                            . '</option>';
                    }
                    ?>
                    </select>
                    <div class="help-text">Hold Ctrl/Cmd to select multiple, use search to find employees</div>
                </div>

                <!-- Badges Select -->
                <div class="mb-4">
                    <label class="select-title">Select Badges</label>
                    <select name="badge_id[]" class="selectpicker w-100" multiple data-live-search="true" data-style="bg-white" data-size="5" title="Choose badges...">
    <?php
    $stmt = $pdo->query("SELECT id, badge_name, description FROM badges");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<option value="' 
            . htmlspecialchars($row['id'], ENT_QUOTES) 
            . '" data-subtext="' 
            . htmlspecialchars($row['description'], ENT_QUOTES) 
            . '">' 
            . htmlspecialchars($row['badge_name'], ENT_QUOTES) 
            . '</option>';
    }
    ?>
</select>
                    <div class="help-text">Select multiple badges to assign to chosen employees</div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-award me-2"></i>Assign Selected Badges
                </button>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
    <!-- Free CDN without kit -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        // Initialize Bootstrap Select
        $(document).ready(function () {
            $('.selectpicker').selectpicker();
            
            // Update counter display
            $('.selectpicker').on('changed.bs.select', function (e) {
                const selectedCount = $(this).find('option:selected').length;
                $(this).siblings('.dropdown-toggle').find('.badge-counter').remove();
                if (selectedCount > 0) {
                    $(this).siblings('.dropdown-toggle').append(
                        `<span class="badge-counter">${selectedCount} selected</span>`
                    );
                }
            });

            // Fade out messages after 5 seconds
            setTimeout(() => {
                $('#message_area').fadeOut(500);
            }, 5000);
        });
    </script>
</body>
</html>