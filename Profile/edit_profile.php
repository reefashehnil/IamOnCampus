<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$profile_error = $profile_success = "";
$pass_error = $pass_success = "";
$error = "";

// Handle Profile Update
if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fname = trim($_POST['fname'] ?? '');
    $mname = trim($_POST['mname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $dept = trim($_POST['dept'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $about_me = trim($_POST['about_me'] ?? '');

    if (!$fname || !$lname || !$dept || !$role || !$email) {
        $profile_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = "Invalid email format.";
    } else {
        $stmt = $conn->prepare("SELECT User_id FROM User_Emails WHERE Email = ? AND User_id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $profile_error = "Email already in use by another account.";
            $stmt->close();
        } else {
            $stmt->close();

            $stmt = $conn->prepare("UPDATE Users SET F_name=?, M_name=?, L_name=?, DeptName=?, Role=?, about_me=? WHERE User_id=?");
            $stmt->bind_param("ssssssi", $fname, $mname, $lname, $dept, $role, $about_me, $user_id);

            if ($stmt->execute()) {
                $stmt->close();

                $stmt2 = $conn->prepare("UPDATE User_Emails SET Email=? WHERE User_id=?");
                $stmt2->bind_param("si", $email, $user_id);
                if ($stmt2->execute()) {
                    $profile_success = "Profile updated successfully.";
                } else {
                    $profile_error = "Failed to update email.";
                }
                $stmt2->close();
            } else {
                $profile_error = "Failed to update profile: " . $stmt->error;
                $stmt->close();
            }
            if (isset($_FILES["dp"]) && $_FILES["dp"]["error"] == 0) {
                $target_dir = "../DP_Uploads/";
                $dp_filename = "dp_" . $user_id . ".jpg";
                $target_file = $target_dir . $dp_filename;

                if ($_FILES["dp"]["size"] <= 2 * 1024 * 1024 && getimagesize($_FILES["dp"]["tmp_name"])) {
                    if (move_uploaded_file($_FILES["dp"]["tmp_name"], $target_file)) {
                        $update_dp_stmt = $conn->prepare("UPDATE Users SET DP = ? WHERE User_id = ?");
                        $update_dp_stmt->bind_param("si", $dp_filename, $user_id);
                        $update_dp_stmt->execute();
                        $profile_error .= " Display picture updated.";
                    } else {
                        $error .= " Failed to move uploaded file.";
                    }
                } else {
                    $error .= " Invalid or too large display picture (max 2MB).";
                }
            }
        }
    }
}

// Handle Password Change
if (isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$current_password || !$new_password || !$confirm_password) {
        $pass_error = "Please fill in all fields.";
    } elseif (strlen($new_password) < 6) {
        $pass_error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $pass_error = "New passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT Passwords FROM Users WHERE User_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($stored_hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current_password, $stored_hash)) {
            $pass_error = "Current password is incorrect.";
        } else {
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE Users SET Passwords = ? WHERE User_id = ?");
            $stmt->bind_param("si", $new_hash, $user_id);
            if ($stmt->execute()) {
                $pass_success = "Password changed successfully!";
            } else {
                $pass_error = "Failed to update password. Try again.";
            }
            $stmt->close();
        }
    }
}

// Fetch user data for form prefilling including email and about_me
$stmt = $conn->prepare("
    SELECT u.F_name, u.M_name, u.L_name, u.DeptName, u.Role, u.DP, ue.Email, u.about_me
    FROM Users u
    INNER JOIN User_Emails ue ON u.User_id = ue.User_id
    WHERE u.User_id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Determine DP path or fallback
$dp_path = "../DP_Uploads/default.jpg";
if (!empty($user['DP']) && file_exists("../DP_Uploads/" . $user['DP'])) {
    $dp_path = "../DP_Uploads/" . $user['DP'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Profile | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e0e0e0;
        }
        .edit-card {
            max-width: 700px;
            margin: auto;
            margin-top: 40px;
            padding: 30px;
            background: #2a2a4a;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
        }
        .form-label {
            font-weight: bold;
            color: #d8b4fe;
        }
        .form-control, .form-select {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border: 1px solid #8b5cf6;
        }
        .form-control:focus, .form-select:focus {
            background-color: #3a3a5a;
            color: #e0e0e0;
            border-color: #a78bfa;
            box-shadow: 0 0 5px rgba(167, 139, 250, 0.5);
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #8b5cf6;
        }
        .nav-tabs .nav-link {
            color: #e0e0e0;
            background-color: #3a3a5a;
            border: 1px solid #8b5cf6;
        }
        .nav-tabs .nav-link.active {
            background-color: #8b5cf6;
            color: #ffffff;
            border-color: #8b5cf6;
        }
        .nav-tabs .nav-link:hover {
            background-color: #4a4a6a;
            border-color: #a78bfa;
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
    </style>
</head>

<body>
    <div class="container">
        <div class="edit-card">
            <h3 class="mb-4 text-center">Edit Your Profile</h3>

            <div class="text-center mb-4">
                <img src="<?= htmlspecialchars($dp_path) ?>" alt="Profile Picture" class="rounded-circle profile-pic" />
            </div>

            <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="edit-profile-tab" data-bs-toggle="tab" data-bs-target="#edit-profile" type="button" role="tab" aria-controls="edit-profile" aria-selected="true">Edit Profile</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="false">Change Password</button>
                </li>
            </ul>

            <div class="tab-content" id="profileTabContent">
                <!-- Edit Profile Tab -->
                <div class="tab-pane fade show active" id="edit-profile" role="tabpanel" aria-labelledby="edit-profile-tab">
                    <?php if ($profile_success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($profile_success) ?></div>
                    <?php elseif ($profile_error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($profile_error) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" novalidate>
                        <input type="hidden" name="action" value="update_profile" />

                        <div class="mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="fname" class="form-control" required value="<?= htmlspecialchars($user['F_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="mname" class="form-control" value="<?= htmlspecialchars($user['M_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="lname" class="form-control" required value="<?= htmlspecialchars($user['L_name']) ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($user['Email']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="dept" class="form-label">Department *</label>
                            <select id="dept" name="dept" class="form-select" required>
                                <?php
                                $departments = [
                                    "Accounting & Finance",
                                    "Economics",
                                    "Management",
                                    "Architecture",
                                    "Civil & Environmental Engineering",
                                    "Electrical & Computer Engineering",
                                    "Mathematics & Physics",
                                    "English & Modern Languages",
                                    "Administration"
                                ];
                                $currentDept = $user['DeptName'] ?? '';
                                foreach ($departments as $department) {
                                    $selected = ($currentDept === $department) ? "selected" : "";
                                    echo "<option value=\"" . htmlspecialchars($department) . "\" $selected>$department</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role *</label>
                            <select name="role" class="form-select" required>
                                <option <?= $user['Role'] === 'Student' ? 'selected' : '' ?>>Student</option>
                                <option <?= $user['Role'] === 'Admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tell me something about yourself</label>
                            <textarea name="about_me" class="form-control" rows="4"><?= htmlspecialchars($user['about_me'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Change Display Picture (JPG only, Max 2MB)</label>
                            <input type="file" name="dp" class="form-control" accept=".jpg,.jpeg,.png" />
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-3">Save Changes</button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-pane fade" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                    <?php if ($pass_success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($pass_success) ?></div>
                    <?php elseif ($pass_error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($pass_error) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <input type="hidden" name="action" value="change_password" />

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required />
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required />
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Change Password</button>
                    </form>
                </div>
            </div>
            <?php
            $role = $_SESSION['role'] ?? '';
            if ($role === 'Admin') {
                $dashboardPath = '../Login/admin_dashboard.php';
            } elseif ($role === 'Student' || $role === 'User') {
                $dashboardPath = '../Login/dashboard.php';
            } else {
                $dashboardPath = '../Login/login.php';
            }
            ?>
            <a href="<?= $dashboardPath ?>" class="btn btn-warning w-25 mx-auto d-block mt-4">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>