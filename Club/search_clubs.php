<?php
session_start();
require '../Connection/db_connect.php';

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";
$query = isset($_GET['q']) ? $_GET['q'] : '';

$stmt = $conn->prepare("SELECT * FROM club WHERE Club_name LIKE ?");
$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Search Clubs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
    <h2 class="text-primary mb-3">Search Clubs</h2>
    <form method="GET" class="d-flex mb-3">
    <input type="text" name="q" class="form-control me-2" placeholder="Search clubs..." value="<?= htmlspecialchars($query) ?>">
    <button type="submit" class="btn btn-primary">
        <i class="bi bi-search"></i> Search
    </button>
</form>

    <table class="table table-striped">
        <thead>
            <tr><th>Club ID</th><th>Club Name</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Club_id'] ?></td>
                    <td><?= htmlspecialchars($row['Club_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
