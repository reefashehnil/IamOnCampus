<?php
session_start();
include '../Connection/db_connect.php';

// Only admin allowed
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

// Sorting option
$sort = $_GET['sort'] ?? 'id';

switch ($sort) {
    case 'dept':
        $order = "u.DeptName ASC, u.F_name ASC, u.L_name ASC";
        break;
    case 'name':
        $order = "u.F_name ASC, u.L_name ASC";
        break;
    default:
        $order = "u.User_id ASC";
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
    <style>
        body {
            background: linear-gradient(135deg, #2c003e 0%, #4b0082 100%);
            color: #e6e6fa;
            padding: 2rem;
        }
        .container {
            background: #3c0a5e;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        h3 {
            color: #d8bfd8;
        }
        .btn-success {
            background-color: #8a2be2;
            border-color: #8a2be2;
        }
        .btn-success:hover {
            background-color: #9932cc;
            border-color: #9932cc;
        }
    .btn-warning {
        background-color: #e6e614ff;
        border-color: #e6e614ff;
        color: #1a1a2e;
    }
    .btn-warning:hover {
        background-color: #ffb300;
        border-color: #ffb300;
    }
    .btn-no-hover {
        background-color: #ffca28; /* Lighter orange for visibility */
        border-color: #ffca28;
        color: #1a1a2e; /* Dark text for contrast */
    }
    .btn-no-hover:hover {
        background-color: #ffca28; /* Same as default to remove hover effect */
        border-color: #ffca28;
        color: #1a1a2e;
    }
        .btn-danger {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }
        .btn-danger:hover {
            background-color: #b71c1c;
            border-color: #b71c1c;
        }
        .form-select {
            background-color: #3c0a5e;
            color: #e6e6fa;
            border: 1px solid #8a2be2;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23d8bfd8'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2rem;
        }
        .form-select:focus {
            border-color: #9932cc;
            box-shadow: 0 0 5px rgba(153, 50, 204, 0.5);
            background-color: #3c0a5e;
            color: #e6e6fa;
        }
        .form-select::placeholder {
            color: #b19cd9;
        }
        .fw-bold {
            color: #d8bfd8;
        }
        .table-dark {
            background-color: #4b0082;
            --bs-table-bg: #4b0082;
        }
        .table {
            background-color: #3c0a5e;
            color: #e6e6fa;
        }
        .table-bordered {
            border-color: #8a2be2;
        }
        .table-bordered th,
        .table-bordered td {
            border-color: #8a2be2;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Manage Users</h3>
        <a href="../Login/admin_dashboard.php" class="btn btn-no-hover">Back to Dashboard</a>
    </div>

    <div class="d-flex justify-content-between mb-3">
        <div>
            <a href="add_user.php" class="btn btn-success">Add User</a>
        </div>
        <form method="get" class="d-flex">
            <label class="me-2 fw-bold">Sort By:</label>
            <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="id" <?= $sort=="id"?"selected":"" ?>>User ID</option>
                <option value="name" <?= $sort=="name"?"selected":"" ?>>Name</option>
                <option value="dept" <?= $sort=="dept"?"selected":"" ?>>Department</option>
            </select>
        </form>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>User ID</th>
                <th>Name</th>
                <th>Department</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
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
                    <td>
                        <div class="d-flex gap-2">
                            <a href="edit_user.php?id=<?= $u['User_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="delete_user.php" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="user_id" value="<?= $u['User_id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
