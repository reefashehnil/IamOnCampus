<?php
session_start();

// Redirect if not logged in or not a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Student') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Dashboard | IamOnCampus</title>
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
            color: #0275d8;
            text-align: center;
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        .user-id-box {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px 20px;
            border-left: 6px solid #0c5460;
            border-radius: 8px;
            max-width: 300px;
            margin: 0 auto 20px;
            font-size: 1.05rem;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .user-id-box .label {
            font-weight: 600;
            margin-right: 6px;
        }
        .user-id-box .id {
            font-family: 'Courier New', Courier, monospace;
            background: #fff;
            padding: 2px 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        /* Center and style edit button container */
        .edit-btn-container {
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-edit-profile {
            font-weight: 600;
            font-size: 1.1rem;
            border-radius: 30px;
            padding: 0.6rem 1.8rem;
            max-width: 220px;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        .btn-edit-profile:hover {
            background-color: #025aa5;
            color: #fff;
            text-decoration: none;
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
            color: #014f86;
            text-decoration: none;
            position: relative;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 16px rgb(0 0 0 / 0.15);
            color: #013e6b;
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
            color: #34515e;
        }
       
        .profile-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
            width: 100%;
            padding: 0 20px 10px;
        }
        .profile-links a {
            display: block;
            text-align: center;
            background: #e3f2fd;
            color: #0275d8;
            font-weight: 600;
            padding: 8px 0;
            border-radius: 8px;
            text-decoration: none;
            box-shadow: 0 1px 3px rgb(0 0 0 / 0.1);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .profile-links a:hover {
            background-color: #0275d8;
            color: #fff;
            text-decoration: none;
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
    <h2 class="welcome-msg">Welcome back, <?= htmlspecialchars($_SESSION['fname']) ?>!</h2>

    <div class="user-id-box">
        <span class="label">Your User ID:</span>
        <span class="id"><?= $_SESSION['user_id'] ?></span>
    </div>


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




        <!-- Skill Management card -->
        <div class="col-md-4">
            <div class="dropdown">
                <a class="card-link text-decoration-none" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <div class="card text-center">
                        <i class="bi bi-tools" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-2 mb-0">Skill Management</h5>
                        <p class="card-text">Manage and explore your skills.</p>
                    </div>
                </a>
                <ul class="dropdown-menu w-100 text-center">
                    <li><a class="dropdown-item" href="../Skills/add_skill.php">Add Skill</a></li>
                    <li><a class="dropdown-item" href="../Skills/my_skills.php">My Skills</a></li>
                    <li><a class="dropdown-item" href="../Skills/available_skills.php">Available Skills</a></li>
                </ul>
            </div>
        </div>

        <!-- Academic Forum card -->
        <a href="forum.php" class="col-md-4 card-link">
            <div class="card text-center">
                <i class="bi bi-journal-text"></i>
                <h5 class="card-title">Academic Forum</h5>
                <p class="card-text">Ask questions & share resources.</p>
            </div>
        </a>

        <!-- Clubs & Events card -->
        <a href="clubs_events.php" class="col-md-4 card-link">
            <div class="card text-center">
                <i class="bi bi-people"></i>
                <h5 class="card-title">Clubs & Events</h5>
                <p class="card-text">Join campus activities & clubs.</p>
            </div>
        </a>

        <!-- Community Discussion card -->
        <a href="community.php" class="col-md-4 card-link">
            <div class="card text-center">
                <i class="bi bi-chat-left-dots"></i>
                <h5 class="card-title">Community Discussion</h5>
                <p class="card-text">Start or join discussions.</p>
            </div>
        </a>

        <!-- Search Platform card -->
        <a href="search.php" class="col-md-4 card-link">
            <div class="card text-center">
                <i class="bi bi-search"></i>
                <h5 class="card-title">Search Platform</h5>
                <p class="card-text">Find skills, events, posts & more.</p>
            </div>
        </a>

        <!-- Notifications card -->
        <a href="../Skills/notifications.php" class="col-md-4 card-link">
            <div class="card text-center">
                <i class="bi bi-bell"></i>
                <h5 class="card-title">Notifications</h5>
                <p class="card-text">View your notifications.</p>
            </div>
        </a>

        <!-- Logout card -->
        <a href="logout.php" class="col-md-4 card-link">
            <div class="card text-center bg-danger text-white" style="height: 150px; border-radius: 12px;">
                <i class="bi bi-box-arrow-right" style="font-size: 3.2rem;"></i>
                <h5 class="card-title">Logout</h5>
                <p class="card-text">Sign out of your account.</p>
            </div>
        </a>
    </div>
</div>

<footer>&copy; 2025 IamOnCampus. All rights reserved.</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
