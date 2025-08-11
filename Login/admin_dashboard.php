<?php
session_start();

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
header("Location: login.php");
exit;
}
include '../Connection/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Admin Dashboard | IamOnCampus</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
<style>
body {
background-color: #e0f7fa;
min-height: 100vh;
display: flex;
flex-direction: column;
font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.dashboard-container {
max-width: 900px;
margin: 50px auto;
flex-grow: 1;
}
.welcome-msg {
font-weight: 700;
color: #007bff;
text-align: center;
margin-bottom: 40px;
font-size: 1.8rem;
}
.card {
cursor: pointer;
transition: transform 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
height: 150px;
border-radius: 12px;
box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
display: flex;
align-items: center;
justify-content: center;
flex-direction: column;
color: #004085;
text-decoration: none;
}
.card:hover {
transform: translateY(-8px);
box-shadow: 0 8px 16px rgb(0 0 0 / 0.15);
color: #002752;
text-decoration: none;
}
.card i {
font-size: 3.2rem;
margin-bottom: 12px;
}
.card-title {
font-weight: 600;
font-size: 1.2rem;
margin-bottom: 6px;
}
.card-text {
font-size: 0.9rem;
color: #2c3e50;
}

footer {
text-align: center;
padding: 15px 0;
background: #b2ebf2;
font-size: 0.9rem;
color: #555;
margin-top: auto;
}
</style>
</head>
<body>
<div class="text-center mb-4" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 28px; font-weight: 600; color: #002072ff; border-bottom: 2px solid #b2dfdb; padding-bottom: 10px;">IamOnCampus</div>
<div class="container dashboard-container">
<h2 class="welcome-msg">Welcome Admin, <?= htmlspecialchars($_SESSION['fname']) ?>!</h2>


<div class="row g-4">

<div class="col-md-4">
  <div class="dropdown">
    <a class="card-link text-decoration-none" data-bs-toggle="dropdown" role="button" aria-expanded="false" href="#">
      <div class="card text-center">
        <i class="bi bi-person-circle" style="font-size: 2rem;"></i>
        <h5 class="card-title mt-2 mb-0">My Profile</h5>
        <p class="card-text">View and edit your profile, posts.</p>
      </div>
    </a>
    <ul class="dropdown-menu w-100 text-center">
      <li><a class="dropdown-item" href="../Profile/view_profile.php">View Profile</a></li>
      <li><a class="dropdown-item" href="../Profile/edit_profile.php">Edit Profile</a></li>
      <li><a class="dropdown-item" href="../Profile/post_content.php">Create Post</a></li>
      <li><a class="dropdown-item" href="../Profile/user_posts.php">View Posts</a></li>
    </ul>
  </div>
</div>


<a href="../User/manage_users.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-people-fill"></i>
<h5 class="card-title">Manage Users</h5>
<p class="card-text">
View all users, add new users, edit user info (including passwords), and delete users.</p>
</div>
</a>

<a href="../Threads/moderate_content.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-pencil-square"></i>
<h5 class="card-title">Moderate Content</h5>
<p class="card-text">Manage forums, skills,posts.</p>
</div>
</a>

<a href="../Reports/admin_reports.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-bar-chart-line-fill"></i>
<h5 class="card-title">View Reports</h5>
<p class="card-text">Usage stats & popular activities.</p>
</div>
</a>

<a href="../Skills/admin_skill.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-tools"></i>
<h5 class="card-title">Skill Management</h5>
<p class="card-text">Approve, add, edit, or delete skills.</p>
</div>
</a>


<!-- Club & Event Management -->
<div class="col-md-4">
    <div class="dropdown">
        <a class="card-link text-decoration-none" data-bs-toggle="dropdown" role="button" aria-expanded="false">
            <div class="card text-center">
                <i class="bi bi-people"></i>
                <h5 class="card-title mt-2 mb-0">Club & Event Management</h5>
                <p class="card-text">Manage campus clubs and events.</p>
            </div>
        </a>
        <ul class="dropdown-menu w-100 text-center">
            <li><a class="dropdown-item" href="../Club/create_club.php">Create Club</a></li>
            <li><a class="dropdown-item" href="../Club/manage_events.php">Manage Events</a></li>
            <li><a class="dropdown-item" href="../Club/search_clubs.php">Search Clubs</a></li>
            <li><a class="dropdown-item" href="../Club/search_events.php">Search Events</a></li>
        </ul>
    </div>
</div>


<!-- Messages -->
<a href="../Chat/chat_list.php" class="col-md-4 card-link position-relative">
    <div class="card text-center">
        <i class="bi bi-envelope"></i>
        <h5 class="card-title">Messages</h5>
        <p class="card-text">Chat with other users.</p>
        <?php
        $msg_stmt = $conn->prepare("SELECT COUNT(*) AS unread_msgs FROM messages WHERE Receiver_id = ? AND Seen_status = 0");
        $msg_stmt->bind_param("i", $user_id);
        $msg_stmt->execute();
        $msg_res = $msg_stmt->get_result()->fetch_assoc();
        if ($msg_res['unread_msgs'] > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="message-notif">
                <?= $msg_res['unread_msgs'] ?>
                <span class="visually-hidden">unread messages</span>
            </span>
        <?php else: ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="message-notif" style="display:none;">
                0
                <span class="visually-hidden">unread messages</span>
            </span>
        <?php endif; ?>
    </div>
</a>

<a href="logout.php" class="col-md-4 card-link">
<div class="card text-center bg-danger text-white" style="height: 150px; border-radius: 12px;">
<i class="bi bi-box-arrow-right" style="font-size: 3.2rem;"></i>
<h5 class="card-title">Logout</h5>
<p class="card-text">Sign out securely.</p>
</div>
</a>
</div>
</div>

<footer>&copy; 2025 IamOnCampus. All rights reserved.</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
setInterval(function(){
    $.get("../Chat/check_new_messages.php", function(data){
        try {
            let res = JSON.parse(data);
            if(res.count && res.count > 0) {
                $("#message-notif").text(res.count).show();
            } else {
                $("#message-notif").hide().text('');
            }
        } catch(e) { console.error(e); }
    });
}, 2000);
</script>
</body>
</html>

