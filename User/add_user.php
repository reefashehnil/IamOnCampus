<?php
session_start();
include '../Connection/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $password_plain = $_POST['password'];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $dept = $_POST['dept'];
    $email = $_POST['email'];

 
    $stmt = $conn->prepare("INSERT INTO Users (F_name, M_name, L_name, Passwords, Role, DeptName) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fname, $mname, $lname, $password_hashed, $role, $dept);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

   
        $stmt_email = $conn->prepare("INSERT INTO User_Emails (User_id, Email) VALUES (?, ?)");
        $stmt_email->bind_param("is", $user_id, $email);
        $stmt_email->execute();

  
        $subject = "Your New IamOnCampus Account";
        $message = "Hello $fname $lname,\n\n".
                   "An account has been created for you on IamOnCampus.\n\n".
                   "Login Email: $email\n".
                   "Password: $password_plain\n\n".
                   "Please log in and change your password immediately for security.\n".
                   "Login here: https://yourwebsite.com/login\n\n".
                   "Regards,\nIamOnCampus Admin";

        $headers = "From: admin@iamoncampus.com\r\n";
        $headers .= "Reply-To: admin@iamoncampus.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        mail($email, $subject, $message, $headers);

     
        $_SESSION['receipt_data'] = [
            'created_at'    => date('Y-m-d H:i:s'),
            'admin_id'      => $_SESSION['user_id'],
            'user_id'       => $user_id,
            'full_name'     => trim($fname . ' ' . ($mname ? $mname . ' ' : '') . $lname),
            'email'         => $email,
            'temp_password' => $password_plain,
            'role'          => $role,
            'dept'          => $dept
        ];

     
        header("Location: user_receipt.php");
        exit;
    } else {
        $_SESSION['error'] = "Failed to add user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add User | Admin - IamOnCampus</title>
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
    max-width: 600px;
    margin: 2rem auto;
}
h2 { color: #d8bfd8; }
.alert-danger {
    background-color: #4b0082;
    border-color: #8a2be2;
    color: #e6e6fa;
}
.form-control {
    background-color: #3c0a5e;
    color: #e6e6fa;
    border: 1px solid #8a2be2;
}
.form-control:focus {
    background-color: #3c0a5e;
    color: #e6e6fa;
    border-color: #9932cc;
    box-shadow: 0 0 5px rgba(153, 50, 204, 0.5);
}
.form-control::placeholder { color: #b19cd9; }
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
.form-select::placeholder { color: #b19cd9; }
label { color: #d8bfd8; }
.btn-success {
    background-color: #8a2be2;
    border-color: #8a2be2;
    color: #fff;
}
.btn-success:hover {
    background-color: #9932cc;
    border-color: #9932cc;
    color: #fff;
}
</style>
</head>
<body>
<div class="container">
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
            <option value="" disabled selected>-- Select Department --</option>
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
    <a href="manage_users.php" class="btn btn-warning">Back</a>
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
