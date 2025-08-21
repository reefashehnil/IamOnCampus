<?php
session_start();
include '../Connection/db_connect.php';

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

// Sorting option
$sort = $_GET['sort'] ?? 'name';   // default to name

switch ($sort) {
    case 'dept':
        $order = "u.DeptName ASC, u.F_name ASC, u.L_name ASC";
        break;
    case 'name':
    default:
        $order = "u.F_name ASC, u.L_name ASC";
        break;
}


// Join users with user_emails
$users = $conn->query("
    SELECT u.User_id, u.F_name, u.M_name, u.L_name, u.DeptName, u.Role, e.Email
    FROM users u
    LEFT JOIN user_emails e ON u.User_id = e.User_id
    ORDER BY $order
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Manage Users</h3>
        <a href="../Login/admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="add_user.php" class="btn btn-success">Add User</a>
        </div>
        <form method="get" class="d-flex">
            <label class="me-2 fw-bold">Sort By:</label>
            <select name="sort" class="form-select" onchange="this.form.submit()">
                
                <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
                <option value="dept" <?= $sort=="dept"?"selected":"" ?>>Department</option>
            </select>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u) { ?>
                <tr>
                    <td><?= htmlspecialchars($u['User_id']) ?></td>
                    <td><?= htmlspecialchars(trim($u['F_name'] . " " . $u['M_name'] . " " . $u['L_name'])) ?></td>
                    <td><?= htmlspecialchars($u['DeptName']) ?></td>
                    <td><?= htmlspecialchars($u['Email'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($u['Role'] ?? '-') ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
