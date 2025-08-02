<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch all skills with user info
$sql = "SELECT s.Skill_id, s.Skill_name, s.Skill_description, s.Availability_time, s.Mode,
               u.F_name, u.L_name, s.User_id
        FROM Skills s
        JOIN Users u ON s.User_id = u.User_id
        ORDER BY s.Skill_name ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Available Skills | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h2>Available Skills</h2>
    <a href="../Login/dashboard.php" class="btn btn-secondary mb-3">Back to Dashboard</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Skill Name</th>
                <th>Description</th>
                <th>Availability</th>
                <th>Mode</th>
                <th>Offered By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($skill = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($skill['Skill_name']) ?></td>
                        <td><?= htmlspecialchars($skill['Skill_description']) ?></td>
                        <td><?= htmlspecialchars($skill['Availability_time']) ?></td>
                        <td><?= htmlspecialchars(ucfirst($skill['Mode'])) ?></td>
                        <td><?= htmlspecialchars($skill['F_name'] . ' ' . $skill['L_name']) ?></td>
                        <td>
                            <?php if ((int)$skill['User_id'] !== (int)$_SESSION['user_id']): ?>
                                <a href="request_skill.php?id=<?= $skill['Skill_id'] ?>" class="btn btn-sm btn-warning">Request</a>

                            <?php else: ?>
                                <!-- No buttons shown for own skills -->
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">No skills available.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
