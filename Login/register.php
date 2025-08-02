<?php
session_start();
include '../Connection/db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = 'Student'; // force role
    $dept = trim($_POST['dept'] ?? '');

    if (!$fname || !$lname || !$email || !$password || !$dept) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check email
        $stmt = $conn->prepare("SELECT User_id FROM User_Emails WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Email already registered.";
            $stmt->close();
        } else {
            $stmt->close();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert into Users
            $stmt = $conn->prepare("INSERT INTO Users (F_name, M_name, L_name, Passwords, Role, DeptName) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fname, $mname, $lname, $hashed_password, $role, $dept);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt->close();

                // Insert email
                $stmt2 = $conn->prepare("INSERT INTO User_Emails (User_id, Email) VALUES (?, ?)");
                $stmt2->bind_param("is", $user_id, $email);
                if ($stmt2->execute()) {
                    $success = "Registration successful! Your User ID is <strong>$user_id</strong>. You can now <a href='login.php'>login</a>.";
                } else {
                    $error = "Failed to save email.";
                }
                $stmt2->close();
            } else {
                $error = "Registration failed: " . $stmt->error;
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Register | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #e0f7fa; }
        .brand-header {
            background-color: #01579b;
            color: white;
            padding: 1rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .container {
            max-width: 500px;
        }
        select.form-select {
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="text-center mb-4" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 28px; font-weight: 600; color: #002072ff; border-bottom: 2px solid #b2dfdb; padding-bottom: 10px;">IamOnCampus</div>


<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h2 class="text-center mb-4">Create Your Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="fname" class="form-label">First Name *</label>
                    <input type="text" id="fname" name="fname" class="form-control" required value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>" />
                </div>
                <div class="col-md-4 mb-3">
                    <label for="mname" class="form-label">Middle Name</label>
                    <input type="text" id="mname" name="mname" class="form-control" value="<?= htmlspecialchars($_POST['mname'] ?? '') ?>" />
                </div>
                <div class="col-md-4 mb-3">
                    <label for="lname" class="form-label">Last Name *</label>
                    <input type="text" id="lname" name="lname" class="form-control" required value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>" />
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address *</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password *</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="6" placeholder="At least 6 characters" />
            </div>

            <div class="mb-3">
                <label for="dept" class="form-label">Department *</label>
                <select class="form-select" id="dept" name="dept" required>
                    <option value="" disabled <?= empty($_POST['dept']) ? 'selected' : '' ?>>-- Select Department --</option>
                    <?php
                    $departments = [
                        "Accounting & Finance",
                        "Economics",
                        "Management",
                        "Architecture",
                        "Civil & Environmental Engineering",
                        "Electrical & Computer Engineering",
                        "Mathematics & Physics",
                        "English & Modern Languages"
                    ];
                    foreach ($departments as $d) {
                        $selected = (isset($_POST['dept']) && $_POST['dept'] == $d) ? "selected" : "";
                        echo "<option value=\"$d\" $selected>$d</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <hr />
        <div class="text-center">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <footer class="mt-4 text-center text-muted">&copy; 2025 IamOnCampus. All rights reserved.</footer>
</div>
</body>
</html>
