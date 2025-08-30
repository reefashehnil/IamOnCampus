<?php
session_start();
include '../Connection/db_connect.php';

$q = $_GET['q'] ?? '';

if ($q !== '') {
    $like = "%$q%";
    $sql = "SELECT DISTINCT ap.Post_id, ap.Acd_title, ap.Acd_content, ap.Course, u.F_name, u.L_name
            FROM academic_posts ap
            JOIN users u ON ap.User_id = u.User_id
            LEFT JOIN tags t ON ap.Post_id = t.Post_id
            WHERE ap.Course LIKE ? OR t.Tag_name LIKE ? OR ap.Acd_title LIKE ? OR ap.Acd_content LIKE ?
            ORDER BY ap.Date_posted DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $like, $like, $like, $like);
} else {
    
    $sql = "SELECT ap.Post_id, ap.Acd_title, ap.Acd_content, ap.Course, u.F_name, u.L_name
            FROM academic_posts ap
            JOIN users u ON ap.User_id = u.User_id
            ORDER BY ap.Date_posted DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Academic Post Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        .card {
            background: #3a2a5a; 
            border: 1px solid #4a3066; 
            color: #fff; 
        }
        .card:hover {
            background: #4a3066;
            transition: 0.2s;
        }
        .card-body {
            background: #3a2a5a; 
        }
        .card-title.fw-bold {
            color: #fff; 
        }
        .text-muted {
            color: #ccc !important; 
        }
        .btn-primary {
            background: #4a3066; 
            border: none;
            color: #fff; 
        }
        .btn-primary:hover {
            background: #5a4080; 
            color: #fff;
        }
        .btn-outline-primary {
            border-color: #7a60a0; 
            color: #e6e6fa; 
            background: #5a4080; 
        }
        .btn-outline-primary:hover {
            background: #7a60a0;
            border-color: #8a70b0; 
            color: #fff; 
        }
        .form-control {
            background: #3a2a5a; 
            border: 1px solid #4a3066;
            color: #fff; 
        }
        .form-control::placeholder {
            color: #ccc; 
        }
        .alert-warning {
            background: #ff6666; 
            color: #fff; 
            border: 1px solid #4a3066; 
        }
    </style>
</head>
<body class="container mt-4">

<a href="../Login/dashboard.php" class="btn btn-warning mb-3">
    Back to Dashboard
</a>

<form method="get" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search by course, tag, or keyword" autofocus>
        <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
    </div>
</form>

<?php if ($results->num_rows): ?>
    <?php while ($row = $results->fetch_assoc()): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['Acd_title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($row['F_name'].' '.$row['L_name']) ?> |
                    <i class="bi bi-book"></i> <?= htmlspecialchars($row['Course']) ?>
                </h6>
                <p class="card-text"><?= nl2br(htmlspecialchars(substr($row['Acd_content'], 0, 150))) ?>...</p>
                <a href="view_academic_post.php?id=<?= $row['Post_id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye"></i> View Post
                </a>
            </div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <div class="alert alert-warning text-center">No results found.</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>