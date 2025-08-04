<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

$stmt = $conn->prepare("
    SELECT p.Content, p.Image_Path, p.Video_Path, p.Timestamp, u.F_name, u.L_name, u.DP 
    FROM userposts p
    JOIN Users u ON p.User_id = u.User_id
    ORDER BY p.Timestamp DESC
");
$stmt->execute();
$posts = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All User Posts | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dp {
            width: 55px;
            height: 55px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 15px;
            border: 2px solid #1877f2; /* Facebook blue border */
        }
        .post-card {
            max-width: 700px;
            margin: 0 auto 30px auto; /* center and add vertical spacing */
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            border: 1px solid #ddd;
            padding: 20px;
            background-color: #fff;
        }
        .username {
            font-weight: 600;
            font-size: 1.2rem;
            color: #050505;
        }
        .post-text {
            font-size: 1rem;
            color: #1c1e21;
            margin-top: 10px;
            margin-bottom: 15px;
            white-space: pre-wrap; /* preserve line breaks */
        }
        img.post-image {
            max-height: 350px;
            width: 100%;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        video.post-video {
            max-height: 350px;
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .timestamp {
            color: #65676b;
            font-size: 0.85rem;
        }
        body {
            background-color: #f0f2f5; /* Facebook light background */
            padding-bottom: 50px;
        }
        .header-bar {
            max-width: 700px;
            margin: 30px auto 20px auto;
            padding: 0 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-bar h3 {
            font-weight: 700;
            color: #050505;
        }
    </style>
</head>
<body>

<div class="header-bar">
    <h3>All User Posts</h3>
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
    <a href="<?= htmlspecialchars($dashboardPath) ?>" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php if ($posts->num_rows > 0): ?>
    <?php while ($row = $posts->fetch_assoc()): ?>
        <div class="post-card">
            <div class="d-flex align-items-center">
                <?php
                $dp = !empty($row['DP']) && file_exists("../DP_uploads/" . $row['DP'])
                    ? "../DP_uploads/" . $row['DP']
                    : "../DP_uploads/default.png";
                ?>
                <img src="<?= htmlspecialchars($dp) ?>" class="dp" alt="DP">
                <div class="username"><?= htmlspecialchars($row['F_name'] . ' ' . $row['L_name']) ?></div>
            </div>

            <div class="post-text"><?= nl2br(htmlspecialchars($row['Content'])) ?></div>

            <?php if (!empty($row['Image_Path']) && file_exists($row['Image_Path'])): ?>
                <img src="<?= htmlspecialchars($row['Image_Path']) ?>" alt="Post Image" class="post-image" />
            <?php endif; ?>

            <?php if (!empty($row['Video_Path']) && file_exists($row['Video_Path'])): ?>
                <video controls class="post-video">
                    <source src="<?= htmlspecialchars($row['Video_Path']) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php endif; ?>

            <div class="timestamp">Posted on <?= date("d M Y, h:i A", strtotime($row['Timestamp'])) ?></div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted text-center" style="margin-top: 50px;">No posts available yet.</p>
<?php endif; ?>

</body>
</html>
