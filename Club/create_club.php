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
            $successMsg = "✅ Club created successfully.";
        } else {
            $errorMsg = "❌ Error: " . $conn->error;
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
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
         Back to Dashboard
    </a>

    <div class="card shadow p-4">
        <h2 class="mb-4 text-primary"><i class="bi bi-people"></i> Create New Club</h2>
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
</body>
</html>
