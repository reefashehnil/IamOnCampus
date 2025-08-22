<?php
session_start();

// Redirect if not logged in or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}
include '../Connection/db_connect.php';

$user_id = $_SESSION['user_id'];
$unreadCount = 0;

$sql = "SELECT COUNT(*) AS unread FROM notifications WHERE User_id = ? AND Seen_status = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $unreadCount = $row['unread'];
}
$stmt->close();

$msg_stmt = $conn->prepare("SELECT COUNT(*) AS unread_msgs FROM messages WHERE Receiver_id = ? AND Seen_status = 0");
$msg_stmt->bind_param("i", $user_id);
$msg_stmt->execute();
$msg_res = $msg_stmt->get_result()->fetch_assoc();
$unread_msgs = $msg_res['unread_msgs'];
$msg_stmt->close();

// Fetch users with about_me for the carousel (limit to keep it light) - can be adjusted for admin data
$users = [];
$u_stmt = $conn->prepare("
    SELECT User_id, F_name, L_name, DP, about_me
    FROM Users
    WHERE TRIM(about_me) <> ''
    ORDER BY User_id DESC
    LIMIT 30
");
$u_stmt->execute();
$u_res = $u_stmt->get_result();
while ($u = $u_res->fetch_assoc()) {
    $users[] = $u;
}
$u_stmt->close();

// Helper to chunk array (3 cards per slide for desktop)
function array_chunk_safe($array, $size) {
    $chunks = [];
    $chunk = [];
    foreach ($array as $item) {
        $chunk[] = $item;
        if (count($chunk) === $size) {
            $chunks[] = $chunk;
            $chunk = [];
        }
    }
    if (!empty($chunk)) $chunks[] = $chunk;
    return $chunks;
}
$slides = array_chunk_safe($users, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #e0e0e0;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background: #2a2a40;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .sidebar.hidden {
            transform: translateX(-250px);
        }
        .sidebar .brand {
            font-size: 24px;
            font-weight: 700;
            color: #a855f7;
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar .nav-link {
            color: #e0e0e0;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #a855f7;
            color: #fff;
        }
        .sidebar .dropdown-menu {
            background: #2a2a40;
            border: 1px solid #4b0082;
            border-radius: 8px;
            width: 200px;
            margin-left: 0;
            left: 0;
            transform: translateX(0);
        }
        .sidebar .dropdown-item {
            color: #e0e0e0;
            padding: 10px 15px;
            white-space: nowrap;
        }
        .sidebar .dropdown-item:hover {
            background: #a855f7;
            color: #fff;
        }
        .sidebar .footer {
            text-align: center;
            padding: 20px 0;
            color: #b0b0c0;
            font-size: 0.95rem;
            border-top: 1px solid #4b0082;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }
        .main-content.full {
            margin-left: 0;
        }
        .hero-section {
            background: linear-gradient(145deg, #2a2a40 0%, #1a1a2e 100%);
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            margin-bottom: 30px;
        }
        .hero-section h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #c084fc;
            margin-bottom: 10px;
        }
        .hero-section p {
            font-size: 1.1rem;
            color: #b0b0c0;
            margin-bottom: 20px;
        }
        .hero-section .btn-explore {
            background: #a855f7;
            color: #fff;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s ease;
        }
        .hero-section .btn-explore:hover {
            background: #9333ea;
        }
        .toggle-sidebar {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #a855f7;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            z-index: 1100;
            display: none;
        }
        /* Carousel styles */
        .carousel-wrap {
            background: #1f1f35;
            border: 1px solid #3a3a58;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 6px 20px rgba(0,0,0,.25);
            margin-bottom: 30px;
        }
        .carousel-title {
            font-weight: 700;
            color: #c084fc;
            margin: 0 0 14px;
            text-align: center;
        }
        .story-card {
            background: #2a2a40;
            border: 1px solid #3a3a58;
            border-radius: 16px;
            padding: 25px;
            width: 350px;
            box-shadow: 0 8px 18px rgba(0,0,0,.35);
        }
        .story-card:nth-child(1) { background-color: #6B46C1; } /* Deep purple */
        .story-card:nth-child(2) { background-color: #805AD5; } /* Medium purple */
        .story-card:nth-child(3) { background-color: #4C51BF; } /* Deep blue */
        .story-card .avatar {
            width: 80px;
            height: 80px;
            border-radius: 9999px;
            object-fit: cover;
            border: 2px solid #7c3aed;
        }
        .story-card .name {
            font-weight: 700;
            color: #eae7ff;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        .story-card .user-id {
            color: #a78bfa;
            font-size: 0.9rem;
            margin: 0 0 8px;
        }
        .story-card .about {
            color: #d7d7e2;
            margin-top: 10px;
            font-size: 0.98rem;
            line-height: 1.35rem;
            display: -webkit-box;
            -webkit-line-clamp: 6;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .carousel .carousel-item {
            transition: transform .6s ease-in-out;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .main-content.full {
                margin-left: 0;
            }
            .toggle-sidebar {
                display: block;
            }
            .story-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <button class="toggle-sidebar"><i class="bi bi-list"></i></button>
    <nav class="sidebar" id="sidebar">
        <div>
            <div class="brand">IamOnCampus</div>
            <ul class="nav flex-column">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="../Profile/view_profile.php" role="button" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> My Profile
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../Profile/view_profile.php">View Profile</a></li>
                        <li><a class="dropdown-item" href="../Profile/edit_profile.php">Edit Profile</a></li>
                        <li><a class="dropdown-item" href="../Profile/post_content.php">Create Post</a></li>
                        <li><a class="dropdown-item" href="../Profile/user_posts.php">View Posts</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../User/manage_users.php">
                        <i class="bi bi-people-fill"></i> Manage Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Threads/moderate_content.php">
                        <i class="bi bi-pencil-square"></i> Moderate Content
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Reports/admin_reports.php">
                        <i class="bi bi-bar-chart-line-fill"></i> View Reports
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Skills/admin_skill.php">
                        <i class="bi bi-tools"></i> Skill Management
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                        <i class="bi bi-people"></i> Club & Event Mgmt
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../Club/create_club.php">Create Club</a></li>
                        <li><a class="dropdown-item" href="../Club/manage_events.php">Manage Events</a></li>
                        <li><a class="dropdown-item" href="../Club/search_clubs.php">Search Clubs</a></li>
                        <li><a class="dropdown-item" href="../Club/search_events.php">Search Events</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../Chat/chat_list.php">
                        <i class="bi bi-envelope"></i> Messages
                        <?php if ($unread_msgs > 0): ?>
                            <span class="badge rounded-pill bg-danger" id="message-notif"><?php echo $unread_msgs; ?></span>
                        <?php else: ?>
                            <span class="badge rounded-pill bg-danger" id="message-notif" style="display:none;">0</span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        <div class="footer">&copy; 2025 IamOnCampus. All rights reserved.</div>
    </nav>

    <div class="main-content" id="main-content">
        <div class="hero-section">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['fname']); ?>!</h1>
            <p>Your User ID: <span style="font-family: 'Roboto Mono', monospace; background: #1a1a2e; padding: 4px 10px; border-radius: 6px; border: 1px solid #4b0082;"><?php echo $_SESSION['user_id']; ?></span></p>
            <p>Manage the IamOnCampus platform with full administrative control!</p>
            <a href="../User/manage_users.php" class="btn-explore">Explore Now</a>
        </div>

        <!-- carousel -->
        <div class="carousel-wrap mt-4">
            <h3 class="carousel-title">Campus Highlights</h3>
            <?php if (count($slides) === 0): ?>
                <p class="text-center text-muted mb-0">No profiles yet. Encourage users to update their profiles to be featured here!</p>
            <?php else: ?>
            <div id="aboutCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
                <div class="carousel-inner">
                    <?php
                    $first = true;
                    foreach ($slides as $slide) :
                    ?>
                    <div class="carousel-item<?php echo $first ? ' active' : ''; ?>">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <?php foreach ($slide as $u):
                                $name = htmlspecialchars(trim(($u['F_name'] ?? '').' '.($u['L_name'] ?? '')));
                                $about = htmlspecialchars($u['about_me'] ?? '');
                                $dpFile = trim($u['DP'] ?? '');
                                $dpPath = $dpFile ? ("../DP_uploads/" . rawurlencode($dpFile)) : "../assets/default_dp.png";
                                echo "<!-- Debug: DP = $dpFile, Path = $dpPath -->";
                            ?>
                            <div class="story-card">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="<?php echo $dpPath; ?>" alt="Profile Picture" class="avatar">
                                    <div>
                                        <div class="name"><?php echo $name ?: 'User'; ?></div>
                                        <div class="user-id">ID: <?php echo $u['User_id']; ?></div>
                                    </div>
                                </div>
                                <div class="about mt-2"><?php echo nl2br($about); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php
                    $first = false;
                    endforeach; ?>
                </div>

                <!-- Controls -->
                <button class="carousel-control-prev" type="button" data-bs-target="#aboutCarousel" data-bs-slide="prev" aria-label="Previous">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#aboutCarousel" data-bs-slide="next" aria-label="Next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
            <?php endif; ?>
        </div>
        <!-- /carousel -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('full');
        });

        // Real-time message notifications
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

        // Ensure auto-cycling keeps running after manual interaction
        const aboutCarousel = document.querySelector('#aboutCarousel');
        if (aboutCarousel) {
            const carousel = new bootstrap.Carousel(aboutCarousel, {
                interval: 8000,
                ride: 'carousel',
                pause: false,
                wrap: true
            });
        }
    </script>
</body>
</html>