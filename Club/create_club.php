<?php
session_start();
require '../Connection/db_connect.php';

if ($_SESSION['role'] !== 'Admin') {
    die("Unauthorized access");
}

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clubName = trim($_POST['club_name']);
    if (!empty($clubName)) {
        $stmt = $conn->prepare("INSERT INTO club (Club_name) VALUES (?)");
        $stmt->bind_param("s", $clubName);
        if ($stmt->execute()) {
            $successMsg = "Club created successfully.";
        } else {
            $errorMsg = "Error: " . $conn->error;
        }
    } else {
        $errorMsg = "⚠ Please enter a club name.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Club</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .container {
            max-width: 700px;
            margin-top: 40px;
            padding: 30px;
            background: #2a2a4a;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        .card {
            background-color: #3a3a5a;
            border: 1px solid #8b5cf6;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        h2 {
            color: #d8b4fe;
            text-align: center;
        }
        .form-label {
            font-weight: bold;
            color: #d8b4fe;
        }
        .form-control {
            background-color: #2a2a4a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .form-control::placeholder {
            color: #b0a8ff;
        }
        .form-control:focus {
            background-color: #2a2a4a;
            color: #e0e0e0;
            border-color: #a78bfa;
            box-shadow: 0 0 5px rgba(167, 139, 250, 0.5);
        }
        .btn-primary {
            background-color: #8b5cf6;
            border-color: #8b5cf6;
        }
        .btn-primary:hover {
            background-color: #a78bfa;
            border-color: #a78bfa;
        }
        .btn-secondary {
            background-color: #4a4a6a;
            border-color: #4a4a6a;
        }
        .btn-secondary:hover {
            background-color: #5a5a7a;
            border-color: #5a5a7a;
        }
        .alert-success {
            background-color: #4a704a;
            color: #d4edda;
            border-color: #4a704a;
        }
        .alert-danger {
            background-color: #703a4a;
            color: #f8d7da;
            border-color: #703a4a;
        }
        .bi {
            color: #d8b4fe;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
         Back to Dashboard
    </a>

    <div class="card shadow p-4">
        <h2 class="mb-4"><i class="bi bi-people"></i> Create New Club</h2>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= $successMsg ?></div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= $errorMsg ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="club_name" class="form-label">Club Name</label>
                <input type="text" name="club_name" id="club_name" class="form-control" placeholder="Enter club name" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Create Club</button>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>