<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}
include '../Connection/db_connect.php';

// ===== 1) Login Stats =====
$login_data = [];
$res = $conn->query("SELECT u.F_name, COUNT(l.Login_id) AS logins
                     FROM login_history l
                     JOIN users u ON l.User_id = u.User_id
                     GROUP BY u.User_id
                     ORDER BY logins DESC");
while ($row = $res->fetch_assoc()) $login_data[] = $row;

// ===== 2) Event Popularity =====
$event_data = [];
$res = $conn->query("SELECT e.Event_title, COUNT(er.Participant_id) AS participants
                     FROM events e
                     LEFT JOIN event_registration er ON e.Event_id = er.Event_id
                     GROUP BY e.Event_id
                     ORDER BY participants DESC");
while ($row = $res->fetch_assoc()) $event_data[] = $row;

// ===== 3) Academic Posts by Replies =====
$academic_data = [];
$res = $conn->query("SELECT a.Acd_title, COUNT(r.Acd_reply_id) AS replies
                     FROM academic_posts a
                     LEFT JOIN replies r ON a.Post_id = r.Post_id
                     GROUP BY a.Post_id
                     ORDER BY replies DESC");
while ($row = $res->fetch_assoc()) $academic_data[] = $row;

// ===== 4) Discussion Threads by Replies =====
$thread_data = [];
$res = $conn->query("SELECT d.Dt_title, COUNT(r.Reply_id) AS replies
                     FROM discussion_threads d
                     LEFT JOIN thread_replies r ON d.Thread_id = r.Thread_id
                     GROUP BY d.Thread_id
                     ORDER BY replies DESC");
while ($row = $res->fetch_assoc()) $thread_data[] = $row;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Platform Reports</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    /* Force same size for all charts */
    canvas {
        width: 100% !important;
        height: 300px !important;
    }
</style>
</head>
<body class="bg-light">

<div class="container py-4">
    <h1 class="mb-4 text-success">Platform Reports & Statistics</h1>
    <a href="../Login/admin_dashboard.php" class="btn btn-secondary mb-4"> Back to Dashboard</a>

    <div class="row">
        <!-- Login Stats -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">Login Statistics</div>
                <div class="card-body"><canvas id="loginChart"></canvas></div>
            </div>
        </div>
        <!-- Event Popularity -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-dark">Most Popular Events</div>
                <div class="card-body"><canvas id="eventChart"></canvas></div>
            </div>
        </div>
        <!-- Academic Replies -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-danger text-white">Top Academic Posts (by Replies)</div>
                <div class="card-body"><canvas id="academicChart"></canvas></div>
            </div>
        </div>
        <!-- Thread Replies -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">Top Discussion Threads (by Replies)</div>
                <div class="card-body"><canvas id="threadChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <?php
    function showTable($title, $data, $headers, $color) {
        echo "<div class='card mb-4'>
                <div class='card-header $color text-white'>$title</div>
                <div class='card-body p-0'>
                    <table class='table table-hover m-0'>
                        <thead class='table-light'><tr>";
        foreach ($headers as $h) echo "<th>$h</th>";
        echo "</tr></thead><tbody>";
        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) echo "<td>$cell</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div></div>";
    }

    showTable("Login Statistics", $login_data, ["User", "Login Count"], "bg-primary");
    showTable("Most Popular Events", $event_data, ["Event", "Participants"], "bg-warning");
    showTable("Top Academic Posts (by Replies)", $academic_data, ["Post", "Replies"], "bg-danger");
    showTable("Top Discussion Threads (by Replies)", $thread_data, ["Thread", "Replies"], "bg-success");
    ?>
</div>

<script>
const loginLabels = <?= json_encode(array_column($login_data, 'F_name')) ?>;
const loginCounts = <?= json_encode(array_column($login_data, 'logins')) ?>;
const eventLabels = <?= json_encode(array_column($event_data, 'Event_title')) ?>;
const eventCounts = <?= json_encode(array_column($event_data, 'participants')) ?>;
const academicLabels = <?= json_encode(array_column($academic_data, 'Acd_title')) ?>;
const academicCounts = <?= json_encode(array_column($academic_data, 'replies')) ?>;
const threadLabels = <?= json_encode(array_column($thread_data, 'Dt_title')) ?>;
const threadCounts = <?= json_encode(array_column($thread_data, 'replies')) ?>;

new Chart(document.getElementById('loginChart'), {
    type: 'bar',
    data: { labels: loginLabels, datasets: [{ label: 'Login Count', data: loginCounts, backgroundColor: 'rgba(54, 162, 235, 0.6)' }] },
    options: { maintainAspectRatio: false }
});

new Chart(document.getElementById('eventChart'), {
    type: 'bar',
    data: { labels: eventLabels, datasets: [{ label: 'Participants', data: eventCounts, backgroundColor: 'rgba(255, 206, 86, 0.6)' }] },
    options: { maintainAspectRatio: false }
});

new Chart(document.getElementById('academicChart'), {
    type: 'pie',
    data: { labels: academicLabels, datasets: [{ label: 'Replies', data: academicCounts, backgroundColor: ['rgba(255,99,132,0.6)','rgba(255,159,64,0.6)','rgba(255,205,86,0.6)','rgba(75,192,192,0.6)'] }] },
    options: { maintainAspectRatio: false }
});

new Chart(document.getElementById('threadChart'), {
    type: 'pie',
    data: { labels: threadLabels, datasets: [{ label: 'Replies', data: threadCounts, backgroundColor: ['rgba(54,162,235,0.6)','rgba(153,102,255,0.6)','rgba(201,203,207,0.6)','rgba(255,99,132,0.6)'] }] },
    options: { maintainAspectRatio: false }
});
</script>

</body>
</html>
