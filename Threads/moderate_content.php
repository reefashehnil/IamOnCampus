<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}
include '../Connection/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderate Content | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2c003e 0%, #4b0082 100%);
            color: #e6e6fa;
            padding: 2rem;
        }
        .container {
            background: #3c0a5e;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
        }
        h1 {
            color: #d8bfd8;
        }
        .card {
            background: #3c0a5e;
            border: 1px solid #8a2be2;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #4b0082;
            border-bottom: 1px solid #8a2be2;
            color: #e6e6fa;
        }
        .table {
            background-color: #3c0a5e;
            color: #e6e6fa;
        }
        .table-bordered {
            border-color: #8a2be2;
        }
        .table-bordered th,
        .table-bordered td {
            border-color: #8a2be2;
        }
        .table thead th {
            background-color: #4b0082;
            color: #e6e6fa;
            border-color: #8a2be2;
        }
        .btn-success {
            background-color: #8a2be2;
            border-color: #8a2be2;
            color: #fff;
        }
        .btn-success:hover {
            background-color: #9932cc;
            border-color: #9932cc;
            color: #fff;
        }
      
        .btn-warning {
            background-color: #ffca28;
            border-color: #ffca28;
            color: #1a1a2e;
        }
        .btn-warning:hover {
            background-color: #ffb300;
            border-color: #ffb300;
            color: #fff;
        }
        .btn-danger {
            background-color: #a71d2a;
            border-color: #a71d2a;
            color: #fff;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
            color: #fff;
        }
        .text-center {
            color: #e6e6fa;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1 class="mb-4">Moderate Content</h1>
    <a href="../Login/admin_dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <!-- Academic Forum -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Academic Forum Posts</span>
            <a href="add_academic_post.php" class="btn btn-success btn-sm">+ Add Post</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Course</th>
                        <th style="width:180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $result = $conn->query("SELECT * FROM academic_posts ORDER BY Post_id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                        <td>" . htmlspecialchars($row['Acd_title']) . "</td>
                        <td>" . htmlspecialchars($row['Acd_content']) . "</td>
                        <td>" . htmlspecialchars($row['Course']) . "</td>
                        <td>
                            <a href='edit_academic_post.php?Post_id={$row['Post_id']}' class='btn btn-sm btn-warning'>Edit</a>
                            <a href='delete_academic_post.php?Post_id={$row['Post_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this post?\")'>Delete</a>
                        </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No academic posts found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Discussion Threads -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Discussion Threads</span>
            <a href="add_discussion_thread.php" class="btn btn-success btn-sm">+ Add Thread</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Tag</th>
                        <th style="width:180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $result = $conn->query("SELECT * FROM discussion_threads ORDER BY Thread_id DESC");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                        <td>" . htmlspecialchars($row['Dt_title']) . "</td>
                        <td>" . htmlspecialchars($row['Dt_content']) . "</td>
                        <td>" . htmlspecialchars($row['Dt_tag']) . "</td>
                        <td>
                            <a href='edit_thread.php?Thread_id={$row['Thread_id']}' class='btn btn-sm btn-warning'>Edit</a>
                            <a href='delete_thread.php?Thread_id={$row['Thread_id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Delete this thread?\")'>Delete</a>
                        </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' class='text-center'>No threads found</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>