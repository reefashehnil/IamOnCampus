<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch only logged-in user's skills
$stmt = $conn->prepare("
    SELECT s.Skill_id, s.Skill_name, s.Mode, u.F_name, u.L_name 
    FROM Skills s 
    JOIN Users u ON s.User_id = u.User_id 
    WHERE s.User_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Skills | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a); 
            font-family: Arial;
            color: #fff; 
        }
        .container {
            margin: 0 auto;
            padding: 15px; 
            background: #2c1e3f; 
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5); 
        }
        h2 {
            color: #fff; 
        }
        .table {
            background: #3a2a5a; 
            color: #fff; 
            border: 1px solid #4a3066; 
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #4a3066; 
        }
        .table thead th {
            background: #4a3066; 
            color: #fff;
        }
        .btn-warning {
            background: #ffcc00; 
            border: none;
            color: #000; 
        }
        .btn-warning:hover {
            background: #e6b800; 
            color: #000;
        }
        .btn-danger {
            background: #ff6666;
            border: none;
            color: #fff; 
        }
        .btn-danger:hover {
            background: #e65c5c; 
            color: #fff;
        }
        .alert-info {
            background: #4a3066;
            color: #fff; 
            border: 1px solid #5a4080; 
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>My Skills</h2>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Skill Name</th>
                    <th>Mode</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['Skill_name']) ?></td>
                    <td><?= htmlspecialchars($row['Mode']) ?></td>
                    <td>
                        <a href="edit_skill.php?id=<?= $row['Skill_id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                        <a href="delete_skill.php?id=<?= $row['Skill_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">You haven’t added any skills yet.</div>
    <?php endif; ?>

    <a href="../Login/dashboard.php" class="btn btn-warning mt-3">Back to Dashboard</a>
</div>
</body>
</html>