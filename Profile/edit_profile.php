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
    $about_me = trim($_POST['about_me'] ?? ''); // New field for about_me

    if (!$fname || !$lname || !$dept || !$role || !$email) {
        $profile_error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profile_error = "Invalid email format.";
    } else {
        // Check if email already exists for another user
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
            $stmt->bind_param("ssssssi", $fname, $mname, $lname, $dept, $role, $about_me, $user_id); // Added about_me

            if ($stmt->execute()) {
                // Update email in User_Emails table
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
            // Handle DP upload
            if (isset($_FILES["dp"]) && $_FILES["dp"]["error"] == 0) {
                $target_dir = "../DP_uploads/";
                $dp_filename = "dp_" . $user_id . ".jpg";
                $target_file = $target_dir . $dp_filename;

                if ($_FILES["dp"]["size"] <= 2 * 1024 * 1024 && getimagesize($_FILES["dp"]["tmp_name"])) {
                    if (move_uploaded_file($_FILES["dp"]["tmp_name"], $target_file)) {
                        // Update DP filename in database
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
$dp_path = "../DP_uploads/default.jpg";
if (!empty($user['DP']) && file_exists("../DP_uploads/" . $user['DP'])) {
    $dp_path = "../DP_uploads/" . $user['DP'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Edit Profile | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .edit-card {
            max-width: 700px;
            margin: auto;
            margin-top: 40px;
            padding: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-label {
            font-weight: bold;
        }
        .profile-pic {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border: 4px solid #0d6efd;
        }
        .nav-tabs .nav-link.active {
            background-color: #0d6efd;
            color: white;
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
                $dashboardPath = '../Login/login.php'; // fallback if not logged in
            }
            ?>
            <a href="<?= $dashboardPath ?>" class="btn btn-secondary w-25 mx-auto d-block mt-4">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>