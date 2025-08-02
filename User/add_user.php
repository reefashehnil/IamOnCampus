<?php
session_start();
include '../Connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $dept = $_POST['dept'];
    $email = $_POST['email'];

    // Insert into Users table
    $stmt = $conn->prepare("INSERT INTO Users (F_name, M_name, L_name, Passwords, Role, DeptName) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $mname, $lname, $password, $role, $dept);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Insert into User_Emails table
        $stmt_email = $conn->prepare("INSERT INTO User_Emails (User_id, Email) VALUES (?, ?)");
        $stmt_email->bind_param("is", $user_id, $email);
        $stmt_email->execute();

        $_SESSION['success'] = "User added successfully.";
        header("Location: manage_users.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add user.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Add User | Admin - IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h2 class="mb-4">Add New User</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>First Name</label>
            <input type="text" name="fname" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Middle Name</label>
            <input type="text" name="mname" class="form-control">
        </div>
        <div class="mb-3">
            <label>Last Name</label>
            <input type="text" name="lname" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
                <option value="Student">Student</option>
                <option value="Admin">Admin</option>
            </select>
        </div>
        <div class="mb-3">
    <label>Department</label>
    <select name="dept" class="form-control" required>
        <option value="">-- Select Department --</option>
        <option value="Accounting & Finance">Accounting & Finance</option>
        <option value="Economics">Economics</option>
        <option value="Management">Management</option>
        <option value="Architecture">Architecture</option>
        <option value="Civil & Environmental Engineering">Civil & Environmental Engineering</option>
        <option value="Electrical & Computer Engineering">Electrical & Computer Engineering</option>
        <option value="Mathematics & Physics">Mathematics & Physics</option>
        <option value="English & Modern Languages">English & Modern Languages</option>
    </select>
</div>
        <button type="submit" class="btn btn-success">Add User</button>
        <a href="manage_users.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
