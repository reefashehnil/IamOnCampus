<?php
session_start();

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
header("Location: login.php");
exit;
}
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

<a href="moderate_content.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-pencil-square"></i>
<h5 class="card-title">Moderate Content</h5>
<p class="card-text">Manage forums, skills, events, posts.</p>
</div>
</a>

<a href="reports.php" class="col-md-4 card-link">
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


<a href="admin_events.php" class="col-md-4 card-link">
<div class="card text-center">
<i class="bi bi-calendar-event-fill"></i>
<h5 class="card-title">Events Management</h5>
<p class="card-text">Create and update campus events.</p>
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

</body>
</html>

