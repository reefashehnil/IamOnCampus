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
<title>Moderate Content</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h1 class="mb-4 text-primary">Moderate Content</h1>
    <a href="../Login/admin_dashboard.php" class="btn btn-secondary mb-4">Back to Dashboard</a>

    <!-- Academic Forum -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>Academic Forum Posts</span>
            <a href="add_academic_post.php" class="btn btn-light btn-sm">+ Add Post</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered m-0">
                <thead class="table-light">
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
                        <td>{$row['Acd_title']}</td>
                        <td>{$row['Acd_content']}</td>
                        <td>{$row['Course']}</td>
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
        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
            <span>Discussion Threads</span>
            <a href="add_discussion_thread.php" class="btn btn-light btn-sm">+ Add Thread</a>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover table-bordered m-0">
                <thead class="table-light">
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
                        <td>{$row['Dt_title']}</td>
                        <td>{$row['Dt_content']}</td>
                        <td>{$row['Dt_tag']}</td>
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

</body>
</html>
