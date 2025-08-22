<?php
session_start();
include '../Connection/db_connect.php';

$q = $_GET['q'] ?? '';

if ($q !== '') {
    $like = "%$q%";
    $sql = "SELECT Thread_id, Dt_title, Dt_content, Dt_tag, Timestamp, F_name, L_name
            FROM discussion_threads dt
            JOIN users u ON dt.User_id = u.User_id
            WHERE Dt_tag LIKE ? OR Dt_title LIKE ? OR Dt_content LIKE ?
            ORDER BY Timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $like, $like, $like);
} else {
    // Fetch all threads if no search term
    $sql = "SELECT Thread_id, Dt_title, Dt_content, Dt_tag, Timestamp, F_name, L_name
            FROM discussion_threads dt
            JOIN users u ON dt.User_id = u.User_id
            ORDER BY Timestamp DESC";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$results = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Discussion Thread Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a); /* Black to dark violet gradient */
            font-family: Arial;
            color: #fff; /* White text for contrast */
        }
        .container {
            margin: 0 auto; /* Original margin */
            padding: 15px; /* Original padding */
            background: #2c1e3f; /* Dark violet shade */
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5); /* Darker shadow for contrast */
        }
        .card {
            background: #3a2a5a; /* Slightly lighter violet for cards */
            border: 1px solid #4a3066; /* Violet border */
            color: #fff; /* White text for card content */
        }
        .card-body {
            background: #3a2a5a; /* Match card background */
        }
        .card-title.fw-bold {
            color: #fff; /* White for thread title */
        }
        .text-muted {
            color: #ccc !important; /* Light gray for muted text (e.g., author, tag) */
        }
        .btn-primary {
            background: #4a3066; /* Violet for search button */
            border: none;
            color: #fff; /* White text */
        }
        .btn-primary:hover {
            background: #5a4080; /* Lighter violet on hover */
            color: #fff;
        }
        .btn-outline-primary {
            border-color: #4a3066; /* Violet border */
            color: #fff; /* White text */
        }
        .btn-outline-primary:hover {
            background: #4a3066; /* Violet background on hover */
            border-color: #5a4080; /* Lighter violet border */
            color: #fff;
        }
        .form-control {
            background: #3a2a5a; /* Dark violet for input */
            border: 1px solid #4a3066; /* Violet border */
            color: #fff; /* White text */
        }
        .form-control::placeholder {
            color: #ccc; /* Light gray placeholder text */
        }
        .alert-warning {
            background: #ff6666; /* Light red for no results */
            color: #fff; /* White text */
            border: 1px solid #4a3066; /* Violet border */
        }
    </style>
</head>
<body class="container mt-4">

<a href="../Login/dashboard.php" class="btn btn-secondary mb-3">
    Back to Dashboard
</a>

<form method="get" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" placeholder="Search by topic or tag" autofocus>
        <button class="btn btn-primary"><i class="bi bi-search"></i> Search</button>
    </div>
</form>

<?php if ($results->num_rows): ?>
    <?php while ($row = $results->fetch_assoc()): ?>
        <div class="card mb-3 shadow-sm">
            <div class="card-body">
                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['Dt_title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">
                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($row['F_name'].' '.$row['L_name']) ?> |
                    <i class="bi bi-tag"></i> <?= htmlspecialchars($row['Dt_tag']) ?>
                </h6>
                <p class="card-text"><?= nl2br(htmlspecialchars(substr($row['Dt_content'], 0, 150))) ?>...</p>
                <a href="view_thread.php?id=<?= $row['Thread_id'] ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-chat-dots"></i> View Thread
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