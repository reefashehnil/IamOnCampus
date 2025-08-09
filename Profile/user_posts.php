<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../Login/login.php");
    exit;
}

// Fetch posts with necessary data
$stmt = $conn->prepare("
    SELECT p.Post_id, p.Content, p.Image_Path, p.Video_Path, p.Timestamp, u.F_name, u.L_name, u.DP 
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
        .dp { width: 55px; height: 55px; object-fit: cover; border-radius: 50%; margin-right: 15px; border: 2px solid #1877f2; }
        .post-card { max-width: 700px; margin: 0 auto 30px auto; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.15); border: 1px solid #ddd; padding: 20px; background-color: #fff; }
        .username { font-weight: 600; font-size: 1.2rem; color: #050505; }
        .post-text { font-size: 1rem; color: #1c1e21; margin-top: 10px; margin-bottom: 15px; white-space: pre-wrap; }
        img.post-image, video.post-video { max-height: 350px; width: 100%; border-radius: 8px; margin-bottom: 15px; object-fit: cover; }
        .timestamp { color: #65676b; font-size: 0.85rem; margin-top: 10px; }
        body { background-color: #f0f2f5; padding-bottom: 50px; }
        .header-bar { max-width: 700px; margin: 30px auto 20px auto; padding: 0 10px; display: flex; justify-content: space-between; align-items: center; }
        .header-bar h3 { font-weight: 700; color: #050505; }
        /* Reaction highlight */
        .reaction-button { cursor: pointer; padding: 5px 8px; border-radius: 4px; border: 1px solid transparent; background-color: #f8f9fa; margin-right: 5px; }
        .reaction-button.active { background-color: #ffeeba; border-color: #ffc107; }
        /* Three-dot menu */
        .comment-options { position: relative; display: inline-block; }
        .three-dots-btn { background: none; border: none; cursor: pointer; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; padding: 0; }
        .three-dots-btn span { width: 4px; height: 4px; background-color: #333; border-radius: 50%; display: inline-block; margin: 0 2px; }
        .comment-options .menu { display: none; position: absolute; background: white; border: 1px solid #ccc; right: 0; min-width: 100px; z-index: 10; box-shadow: 0px 2px 6px rgba(0,0,0,0.15); }
        .comment-options .menu a { display: block; padding: 5px 10px; text-decoration: none; color: black; }
        .comment-options .menu a:hover { background: #f1f1f1; }
        .comment-options.show .menu { display: block; }
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

            <!-- Reaction Buttons -->
            <div class="mt-2">
                <?php
                $userReaction = null;
                $checkReaction = $conn->prepare("SELECT Reaction_type FROM post_reactions WHERE Post_id = ? AND User_id = ?");
                $checkReaction->bind_param("ii", $row['Post_id'], $_SESSION['user_id']);
                $checkReaction->execute();
                $res = $checkReaction->get_result();
                if ($r = $res->fetch_assoc()) $userReaction = $r['Reaction_type'];
                $checkReaction->close();

                $reactions = ['like' => '👍', 'love' => '❤️', 'care' => '🤗', 'haha' => '😂', 'wow' => '😮', 'sad' => '😢', 'angry' => '😡'];
                foreach ($reactions as $type => $emoji): ?>
                    <form action="react_post.php" method="POST" class="d-inline-block">
                        <input type="hidden" name="post_id" value="<?= $row['Post_id'] ?>">
                        <input type="hidden" name="reaction_type" value="<?= $type ?>">
                        <button type="submit" class="reaction-button <?= ($userReaction === $type) ? 'active' : '' ?>" onclick="highlightReaction('<?= $row['Post_id'] ?>','<?= $type ?>')">
                            <?= $emoji ?>
                        </button>
                    </form>
                <?php endforeach; ?>
            </div>

            <!-- Comment Form -->
            <form action="comment_post.php" method="POST" class="mt-3">
                <input type="hidden" name="post_id" value="<?= $row['Post_id'] ?>">
                <input type="hidden" name="action" value="add_comment">
                <div class="input-group">
                    <input type="text" name="comment" class="form-control" placeholder="Write a comment..." required>
                    <button type="submit" class="btn btn-outline-primary btn-sm">Comment</button>
                </div>
            </form>

            <!-- Show Comments -->
            <?php
            $commentStmt = $conn->prepare("
                SELECT c.Comment_id, c.Comment_text, c.Timestamp, c.User_id, u.F_name, u.L_name
                FROM post_comments c
                JOIN users u ON c.User_id = u.User_id
                WHERE c.Post_id = ?
                ORDER BY c.Timestamp ASC
            ");
            $commentStmt->bind_param("i", $row['Post_id']);
            $commentStmt->execute();
            $commentResult = $commentStmt->get_result();

            while ($comment = $commentResult->fetch_assoc()):
            ?>
                <div class="mt-2 p-2 border rounded bg-light">
                    <strong><?= htmlspecialchars($comment['F_name'] . ' ' . $comment['L_name']) ?></strong> 
                    <small class="text-muted"><?= $comment['Timestamp'] ?></small>
                    <p><?= htmlspecialchars($comment['Comment_text']) ?></p>

                    <?php if ($_SESSION['user_id'] == $comment['User_id']): ?>
                        <div class="comment-options">
                            <button type="button" class="three-dots-btn">
                                <span></span><span></span><span></span>
                            </button>
                            <div class="menu">
                                <a href="#" onclick="event.preventDefault(); document.getElementById('edit-form-<?= $comment['Comment_id'] ?>').style.display='block';">Edit</a>
                                <a href="#" onclick="if(confirm('Delete this comment?')) { document.getElementById('delete-form-<?= $comment['Comment_id'] ?>').submit(); }">Delete</a>
                            </div>
                        </div>

                        <!-- Hidden Edit Form -->
                        <form id="edit-form-<?= $comment['Comment_id'] ?>" action="comment_post.php" method="POST" class="mt-1" style="display:none;">
                            <input type="hidden" name="action" value="edit_comment">
                            <input type="hidden" name="comment_id" value="<?= $comment['Comment_id'] ?>">
                            <input type="text" name="comment" value="<?= htmlspecialchars($comment['Comment_text']) ?>" class="form-control form-control-sm mb-1" required>
                            <button type="submit" class="btn btn-sm btn-outline-success">Save</button>
                        </form>

                        <!-- Hidden Delete Form -->
                        <form id="delete-form-<?= $comment['Comment_id'] ?>" action="comment_post.php" method="POST" class="d-none">
                            <input type="hidden" name="action" value="delete_comment">
                            <input type="hidden" name="comment_id" value="<?= $comment['Comment_id'] ?>">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
            <div class="timestamp">Posted on <?= date("d M Y, h:i A", strtotime($row['Timestamp'])) ?></div>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted text-center" style="margin-top: 50px;">No posts available yet.</p>
<?php endif; ?>

<script>
function highlightReaction(postId, reactionType) {
    document.querySelectorAll('.reaction-button').forEach(btn => {
        let pid = btn.closest('form').querySelector('input[name="post_id"]').value;
        if (pid == postId) btn.classList.remove('active');
    });
    let target = document.querySelector(`.reaction-button[onclick*="'${postId}'"][onclick*="'${reactionType}'"]`);
    if (target) target.classList.add('active');
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.three-dots-btn')) {
        let parent = e.target.closest('.comment-options');
        document.querySelectorAll('.comment-options').forEach(opt => {
            if (opt !== parent) opt.classList.remove('show');
        });
        parent.classList.toggle('show');
    } else {
        document.querySelectorAll('.comment-options').forEach(opt => opt.classList.remove('show'));
    }
});
</script>

</body>
</html>
