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
                        <a href="../Login/dashboard.php" class="btn btn-secondary mt">Back to Dashboard</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">You haven’t added any skills yet.</div>
    <?php endif; ?>
</div>
</body>
</html>
