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
    $role = 'Student'; 
    $dept = trim($_POST['dept'] ?? '');

    if (!$fname || !$lname || !$email || !$password || !$dept) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
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

            $stmt = $conn->prepare("INSERT INTO Users (F_name, M_name, L_name, Passwords, Role, DeptName) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fname, $mname, $lname, $hashed_password, $role, $dept);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt->close();

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
<meta charset="UTF-8">
<title>Register | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
body {
    background: linear-gradient(135deg, #0d0d0d 0%, #4b0082 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #fff;
    overflow-x: hidden;
}

/* Particles */
.particles { position: fixed; top:0; left:0; width:100%; height:100%; pointer-events:none; z-index:1; }
.particle { position:absolute; width:4px; height:4px; background: rgba(200,200,255,0.7); border-radius:50%; animation: float 6s ease-in-out infinite;}
@keyframes float {0%,100% {transform: translateY(0) rotate(0deg); opacity:0;} 50% {transform: translateY(-100px) rotate(180deg); opacity:1;}}

/* Header */
.brand-header { font-size:36px; font-weight:700; color:#e0c3fc; text-align:center; padding:20px 0; position:relative; z-index:10; text-shadow:2px 2px 4px rgba(0,0,0,0.7);}
.brand-header::after { content:''; position:absolute; bottom:0; left:50%; transform:translateX(-50%); width:100px; height:3px; background: linear-gradient(90deg,#8a2be2,#4b0082); border-radius:2px;}

/* Card */
.container { max-width:500px; }
.card { background: rgba(30,0,50,0.95); backdrop-filter: blur(10px); border:none; border-radius:20px; box-shadow:0 15px 35px rgba(0,0,0,0.6); transition:all 0.3s ease; position:relative; z-index:10; }
.card:hover { transform: translateY(-5px); box-shadow:0 20px 40px rgba(0,0,0,0.5); }
.card h2 {color:#e0c3fc;}
.card label, .card .form-label { color:#fff; }
.card a { color:#8a2be2; font-weight:600; text-decoration:none; }

/* Inputs */
.form-control, .form-select {
    border: 2px solid #4b0082;
    border-radius: 10px;
    padding: 12px 15px;
    transition: all 0.3s ease;
    background: rgba(50,0,50,0.9);
    color: #fff !important;
}
.form-control:focus, .form-select:focus {
    border-color: #8a2be2;
    box-shadow: 0 0 0 3px rgba(138,43,226,0.2);
    background: rgba(60,0,60,1);
    transform: scale(1.02);
    color: #fff !important;
}

/* Password field adjustments */
.position-relative { position:relative; }
.password-input { padding-right: 45px; color: #fff !important; }
.input-icon { position:absolute; top:50%; right:15px; transform:translateY(-50%); cursor:pointer; color:#fff !important; font-size:1.1rem; pointer-events:auto; }

/* Buttons */
.btn-primary { background: linear-gradient(135deg,#4b0082 0%,#8a2be2 100%); border:none; border-radius:10px; padding:12px; font-weight:600; transition:all 0.3s ease; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(138,43,226,0.4); }

/* Alerts */
.alert { border-radius:10px; background-color:#2a002a; color:#f0bfff; }

/* Footer */
footer { color:#e0c3fc; text-align:center; margin-top:20px; }
</style>
</head>
<body>

<div class="particles" id="particles"></div>

<div class="brand-header"><i class="fas fa-graduation-cap me-2"></i>IamOnCampus</div>

<div class="container mt-4">
    <div class="card shadow-sm p-4">
        <h2 class="text-center mb-4"><i class="fas fa-user-plus me-2" style="color:#8a2be2;"></i>Create Your Account</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" novalidate>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="fname" class="form-label">First Name *</label>
                    <input type="text" id="fname" name="fname" class="form-control" required value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="mname" class="form-label">Middle Name</label>
                    <input type="text" id="mname" name="mname" class="form-control" value="<?= htmlspecialchars($_POST['mname'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="lname" class="form-label">Last Name *</label>
                    <input type="text" id="lname" name="lname" class="form-control" required value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>">
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email address *</label>
                <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>

            <div class="mb-3 position-relative">
                <input type="password" id="password" name="password" class="form-control password-input" required minlength="6" placeholder="At least 6 characters">
                <!-- Single eye icon -->
                <i class="fas fa-eye input-icon" id="togglePassword"></i>
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
                        $selected = (isset($_POST['dept']) && $_POST['dept']==$d) ? "selected" : "";
                        echo "<option value=\"$d\" $selected>$d</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>

        <hr style="margin:30px 0;">
        <div class="text-center">
            <p style="color:#ffffff;">Already have an account? 
                <a href="login.php" style="color:#8a2be2; font-weight:600; text-decoration:none;">Login here <i class="fas fa-arrow-right ms-1"></i></a>
            </p>
        </div>
    </div>

    <footer class="mt-4">&copy; 2025 IamOnCampus. All rights reserved.</footer>
</div>

<script>
// Particles
function createParticles() {
    const particlesContainer = document.getElementById('particles');
    for(let i=0;i<50;i++){
        const p = document.createElement('div');
        p.className='particle';
        p.style.left = Math.random()*100+'%';
        p.style.animationDelay=Math.random()*6+'s';
        p.style.animationDuration=(Math.random()*4+4)+'s';
        particlesContainer.appendChild(p);
    }
}
window.addEventListener('load', createParticles);

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this;
    if(passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
});
</script>

</body>
</html>
