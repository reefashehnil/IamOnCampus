<?php
session_start();
include '../Connection/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>IamOnCampus — Home</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root {
--overlay: rgba(0,0,0,.45);
}
body,html { height: 100%; }
.hero {
  position: relative;
  min-height: 100vh;
  display: grid;
  place-items: center;
  text-align: center;
  color: #fff;
  background: url('../DP_uploads/students-studying-outdoors-stockcake.png') center/cover no-repeat,
              linear-gradient(135deg,#0d6efd 0%, #6610f2 100%);
}

.hero::before {
content: "";
position: absolute;
inset: 0;
background: var(--overlay);
}
.hero-inner {
position: relative;
z-index: 1;
padding: 2rem;
}
.brand-title {
font-weight: 800;
letter-spacing: .5px;
text-shadow: 0 2px 10px rgba(0,0,0,.35);
}
.nav-link { font-weight: 500; }
.icon-box {
  font-size: 2rem;
  color: #ffc107;
  margin-bottom: 12px;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(135deg, #000000 0%, #4b0082 100%);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="./">IamOnCampus</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsMain" aria-controls="navbarsMain" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarsMain">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="#about">About us</a></li>
        <?php if (empty($_SESSION['user_id'])): ?>
        <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Login</a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="loginDropdown">
            <li><a class="dropdown-item" href="login.php">User Login</a></li>
            <li><a class="dropdown-item" href="login.php">Admin Login</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item">
          <form method="post" action="logout.php" class="mb-0">
            <button class="btn btn-sm btn-outline-light ms-lg-2" type="submit">Logout</button>
          </form>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>



<header class="hero">
<div class="hero-inner container">
<div class="row justify-content-center">
<div class="col-12 col-lg-9">
<h1 class="display-3 brand-title">IamOnCampus</h1>
<p class="lead mb-4">Connect. Learn. Collaborate. Everything happening on your campus — in one place.</p>
<div class="d-flex gap-2 justify-content-center flex-wrap">

</div>
</div>
</div>
</div>
</header>



<section id="about" class="py-5" style="background: linear-gradient(135deg, #000000 0%, #4b0082 100%); color: #fff;">
  <div class="container text-center">
    <h2 class="mb-4 fw-bold text-warning">Why Choose IamOnCampus?</h2>
    <p class="mb-5 fs-5" style="color: #e0e0e0;">
      More than just a campus portal — IamOnCampus is your digital hub for academic growth, 
      social engagement, and personal development. Our platform empowers students to share knowledge, 
      connect with peers, join exciting events, and build skills that go beyond the classroom.
    </p>
    <div class="row g-4">
      <div class="col-md-3">
        <div class="p-4 h-100 shadow-sm rounded" style="background: rgba(255,255,255,0.1);">
          <div class="icon-box"><i class="fas fa-graduation-cap text-warning"></i></div>
          <h5 class="fw-bold">Academic Community</h5>
          <p>Engage in discussions, share resources, and learn together with peers across disciplines.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 h-100 shadow-sm rounded" style="background: rgba(255,255,255,0.1);">
          <div class="icon-box"><i class="fas fa-users text-warning"></i></div>
          <h5 class="fw-bold">Clubs & Events</h5>
          <p>Discover clubs, register for events, and be part of vibrant student activities on campus.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 h-100 shadow-sm rounded" style="background: rgba(255,255,255,0.1);">
          <div class="icon-box"><i class="fas fa-lightbulb text-warning"></i></div>
          <h5 class="fw-bold">Skill Sharing</h5>
          <p>Offer your skills, find mentors, or collaborate with others to grow professionally.</p>
        </div>
      </div>
      <div class="col-md-3">
        <div class="p-4 h-100 shadow-sm rounded" style="background: rgba(255,255,255,0.1);">
          <div class="icon-box"><i class="fas fa-comments text-warning"></i></div>
          <h5 class="fw-bold">Stay Connected</h5>
          <p>Chat, share posts, and get updates so you never miss out on what’s happening around you.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<footer class="py-4 text-center text-light" style="background: linear-gradient(135deg, #000000 0%, #4b0082 100%);">
  <div class="container small">
    &copy; <?php echo date('Y'); ?> IamOnCampus — All rights reserved.
  </div>
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
