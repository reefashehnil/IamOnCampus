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
      background: url('DP_uploads/post_6890c8796ae8c_college-75535_1280.jpg') center/cover no-repeat,
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
    .glass-card {
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.2);
      border-radius: 1.25rem;
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      box-shadow: 0 10px 30px rgba(0,0,0,.25);
    }
    #about { scroll-margin-top: 80px; }
  </style>
</head>
<body>
  <!-- NAVBAR -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
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

  <!-- HERO -->
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

  <!-- ABOUT -->
  <section id="about" class="py-5 bg-light">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
          <div class="p-4 p-md-5 glass-card text-white" style="background: linear-gradient(135deg, rgba(13,110,253,.6), rgba(102,16,242,.6));">
            <h2 class="h1 mb-3">About IamOnCampus</h2>
            <p class="mb-3">
              IamOnCampus is a student-first platform that brings your university life together —
              academic discussions, clubs & events, skill sharing, messaging, and a social feed.
              Students can follow courses, join clubs, register for events, request or offer skills,
              and keep in touch through threads, posts, comments, and DMs. Admins get a streamlined
              dashboard to oversee content, clubs, and reports.
            </p>
            <div class="row g-3">
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-white border-opacity-25">
                  <h3 class="h5 mb-1">Learn & Discuss</h3>
                  <p class="mb-0">Share academic posts, tag topics, and reply in organized threads.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-white border-opacity-25">
                  <h3 class="h5 mb-1">Clubs & Events</h3>
                  <p class="mb-0">Explore clubs, view upcoming events, and register with a tap.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-white border-opacity-25">
                  <h3 class="h5 mb-1">Skills Marketplace</h3>
                  <p class="mb-0">Offer your skills or request help — from programming to design.</p>
                </div>
              </div>
              <div class="col-md-6">
                <div class="p-3 rounded-4 border border-white border-opacity-25">
                  <h3 class="h5 mb-1">Messages & Notifications</h3>
                  <p class="mb-0">Chat with peers and get real-time updates on what matters.</p>
                </div>
              </div>
            </div>
            <div class="mt-4">
              <a href="register.php" class="btn btn-warning me-2">Get started</a>
              <a href="login.php" class="btn btn-outline-light">I already have an account</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer class="py-4 bg-dark text-center text-white-50">
    <div class="container small">
      &copy; <?php echo date('Y'); ?> IamOnCampus — All rights reserved.
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
