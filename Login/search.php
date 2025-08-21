<?php
session_start();
include '../Connection/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$search = "";
$results = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['search'])) {
    $search = trim($_POST['search']);
    $stmt = $conn->prepare("
        SELECT User_id, F_name, M_name, L_name, DeptName 
        FROM users 
        WHERE User_id LIKE ? 
           OR F_name LIKE ? 
           OR M_name LIKE ? 
           OR L_name LIKE ?
        ORDER BY User_id ASC
        LIMIT 100
    ");
    $like = "%$search%";
    $stmt->bind_param("ssss", $like, $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Show all users if no search input
    $results = $conn->query("SELECT User_id, F_name, M_name, L_name, DeptName FROM users ORDER BY User_id ASC")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Search Users</h3>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <form method="post" class="mb-3">
            <input type="text" name="search" class="form-control" placeholder="Enter User ID or Name" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-primary mt-2">Search</button>
        </form>

        <?php if (!empty($results)) { ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['User_id']) ?></td>
                            <td><?= htmlspecialchars(trim($row['F_name'] . " " . $row['M_name'] . " " . $row['L_name'])) ?></td>
                            <td><?= htmlspecialchars($row['DeptName']) ?></td>
                            <td><a href="../Profile/view_profile.php?user_id=<?= urlencode($row['User_id']) ?>" class="btn btn-sm btn-info">View</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="text-danger">No users found.</p>
        <?php } ?>
    </div>
</body>
</html>
