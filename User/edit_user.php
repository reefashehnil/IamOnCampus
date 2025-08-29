<?php
session_start();
include '../Connection/db_connect.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: manage_users.php");
    exit;
}

$user_id = $_GET['id'];
$error = '';
$success = '';

// Fetch user info
$user_sql = "SELECT * FROM Users WHERE User_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {
    $_SESSION['error'] = "User not found.";
    header("Location: manage_users.php");
    exit;
}

$user = $user_result->fetch_assoc();

// Fetch email
$email_sql = "SELECT Email FROM User_Emails WHERE User_id = ?";
$email_stmt = $conn->prepare($email_sql);
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_result = $email_stmt->get_result();
$email_row = $email_result->fetch_assoc();
$email = $email_row['Email'] ?? '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname = trim($_POST['fname']);
    $mname = trim($_POST['mname']);
    $lname = trim($_POST['lname']);
    $role = trim($_POST['role']);
    $dept = trim($_POST['dept']);
    $email_input = trim($_POST['email']);

    // Update Users table
    $update_user_sql = "UPDATE Users SET F_name=?, M_name=?, L_name=?, Role=?, DeptName=? WHERE User_id=?";
    $update_user_stmt = $conn->prepare($update_user_sql);
    $update_user_stmt->bind_param("sssssi", $fname, $mname, $lname, $role, $dept, $user_id);
    $update_user_stmt->execute();

    // Update or insert into User_Emails
    $check_email_sql = "SELECT * FROM User_Emails WHERE User_id = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("i", $user_id);
    $check_email_stmt->execute();
    $email_exists = $check_email_stmt->get_result()->num_rows > 0;

    if ($email_exists) {
        $update_email_sql = "UPDATE User_Emails SET Email = ? WHERE User_id = ?";
        $update_email_stmt = $conn->prepare($update_email_sql);
        $update_email_stmt->bind_param("si", $email_input, $user_id);
        $update_email_stmt->execute();
    } else {
        $insert_email_sql = "INSERT INTO User_Emails (User_id, Email) VALUES (?, ?)";
        $insert_email_stmt = $conn->prepare($insert_email_sql);
        $insert_email_stmt->bind_param("is", $user_id, $email_input);
        $insert_email_stmt->execute();
    }

    $_SESSION['success'] = "User updated successfully.";
    header("Location: manage_users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User | Admin - IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c003e 0%, #4b0082 100%);
            color: #e6e6fa;
            padding: 2rem;
        }
        .container {
            max-width: 600px;
            background: #3c0a5e;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        h2 {
            color: #d8bfd8;
        }
        .btn-primary {
            background-color: #8a2be2;
            border-color: #8a2be2;
        }
        .btn-primary:hover {
            background-color: #9932cc;
            border-color: #9932cc;
        }

        .form-control, .form-select {
            background-color: #3c0a5e;
            color: #e6e6fa;
            border: 1px solid #8a2be2;
        }
        .form-control:focus, .form-select:focus {
            border-color: #9932cc;
            box-shadow: 0 0 5px rgba(153, 50, 204, 0.5);
            background-color: #3c0a5e;
            color: #e6e6fa;
        }
        .form-control::placeholder, .form-select::placeholder {
            color: #b19cd9;
        }
        .form-label {
            color: #d8bfd8;
        }
        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23d8bfd8'%3E%3Cpath d='M7 10l5 5 5-5H7z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            padding-right: 2rem;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit User</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($user['F_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" name="mname" class="form-control" value="<?= htmlspecialchars($user['M_name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="lname" class="form-control" value="<?= htmlspecialchars($user['L_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="Student" <?= $user['Role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                <option value="Admin" <?= $user['Role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Department</label>
            <select name="dept" class="form-select" required>
                <option value="">Select Department</option>
                <option value="Accounting & Finance" <?= $user['DeptName'] === 'Accounting & Finance' ? 'selected' : '' ?>>Accounting & Finance</option>
                <option value="Economics" <?= $user['DeptName'] === 'Economics' ? 'selected' : '' ?>>Economics</option>
                <option value="Management" <?= $user['DeptName'] === 'Management' ? 'selected' : '' ?>>Management</option>
                <option value="Architecture" <?= $user['DeptName'] === 'Architecture' ? 'selected' : '' ?>>Architecture</option>
                <option value="Civil & Environmental Engineering" <?= $user['DeptName'] === 'Civil & Environmental Engineering' ? 'selected' : '' ?>>Civil & Environmental Engineering</option>
                <option value="Electrical & Computer Engineering" <?= $user['DeptName'] === 'Electrical & Computer Engineering' ? 'selected' : '' ?>>Electrical & Computer Engineering</option>
                <option value="Mathematics & Physics" <?= $user['DeptName'] === 'Mathematics & Physics' ? 'selected' : '' ?>>Mathematics & Physics</option>
                <option value="English & Modern Languages" <?= $user['DeptName'] === 'English & Modern Languages' ? 'selected' : '' ?>>English & Modern Languages</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="manage_users.php" class="btn btn-warning">Cancel</a>
    </form>
</div>
</body>
</html>
