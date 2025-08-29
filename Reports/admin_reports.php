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
    body {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        color: #e0e0e0;
    }
    .container {
        margin-top: 40px;
        padding: 30px;
        background: #2a2a4a;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(138, 43, 226, 0.3);
    }
    h1 {
        color: #d8b4fe;
        text-align: center;
    }
    .btn-secondary {
        background-color: #4a4a6a;
        border-color: #4a4a6a;
    }
    .btn-secondary:hover {
        background-color: #5a5a7a;
        border-color: #5a5a7a;
    }
    .card {
        background-color: #3a3a5a;
        border: 1px solid #8b5cf6;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
    }
    .card-header {
        background-color: #2a2a4a !important;
        color: #d8b4fe;
        border-bottom: 1px solid #8b5cf6;
    }
    canvas {
        width: 100% !important;
        height: 300px !important;
    }
    .table {
        background-color: #3a3a5a;
        color: #e0e0e0;
        border: 1px solid #8b5cf6;
    }
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: #4a4a6a;
    }
    .table-hover tbody tr:hover {
        background-color: #5a5a7a;
    }
    th {
        color: #d8b4fe;
        background-color: #2a2a4a;
        border-color: #8b5cf6;
    }
    td {
        color: #e0e0e0;
        border-color: #8b5cf6;
    }
    .table-light {
        background-color: #2a2a4a !important;
        color: #d8b4fe;
    }
</style>
</head>
<body>

<div class="container py-4">
    <h1 class="mb-4">Platform Reports & Statistics</h1>
    <a href="../Login/admin_dashboard.php" class="btn btn-warning mb-4">Back to Dashboard</a>

    <div class="row">
        <!-- Login Stats -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Login Statistics</div>
                <div class="card-body"><canvas id="loginChart"></canvas></div>
            </div>
        </div>
        <!-- Event Popularity -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Most Popular Events</div>
                <div class="card-body"><canvas id="eventChart"></canvas></div>
            </div>
        </div>
        <!-- Academic Replies -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Top Academic Posts (by Replies)</div>
                <div class="card-body"><canvas id="academicChart"></canvas></div>
            </div>
        </div>
        <!-- Thread Replies -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">Top Discussion Threads (by Replies)</div>
                <div class="card-body"><canvas id="threadChart"></canvas></div>
            </div>
        </div>
    </div>

    <!-- Tables Section -->
    <?php
    function showTable($title, $data, $headers, $color) {
        echo "<div class='card mb-4'>
                <div class='card-header'>$title</div>
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

    showTable("Login Statistics", $login_data, ["User", "Login Count"], "");
    showTable("Most Popular Events", $event_data, ["Event", "Participants"], "");
    showTable("Top Academic Posts (by Replies)", $academic_data, ["Post", "Replies"], "");
    showTable("Top Discussion Threads (by Replies)", $thread_data, ["Thread", "Replies"], "");
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
    data: { 
        labels: loginLabels, 
        datasets: [{ 
            label: 'Login Count', 
            data: loginCounts, 
            backgroundColor: '#00b7eb' 
        }] 
    },
    options: { 
        maintainAspectRatio: false,
        scales: {
            x: { ticks: { color: '#e0e0e0' } },
            y: { ticks: { color: '#e0e0e0' } }
        },
        plugins: {
            legend: { labels: { color: '#e0e0e0' } }
        }
    }
});

new Chart(document.getElementById('eventChart'), {
    type: 'bar',
    data: { 
        labels: eventLabels, 
        datasets: [{ 
            label: 'Participants', 
            data: eventCounts, 
            backgroundColor: '#eaf20fff' 
        }] 
    },
    options: { 
        maintainAspectRatio: false,
        scales: {
            x: { ticks: { color: '#e0e0e0' } },
            y: { ticks: { color: '#e0e0e0' } }
        },
        plugins: {
            legend: { labels: { color: '#e0e0e0' } }
        }
    }
});

new Chart(document.getElementById('academicChart'), {
    type: 'pie',
    data: { 
        labels: academicLabels, 
        datasets: [{ 
            label: 'Replies', 
            data: academicCounts, 
            backgroundColor: ['#ff1744', '#ff9100', '#ffeb3b', '#00e676', '#18ffff'] 
        }] 
    },
    options: { 
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#e0e0e0' } }
        }
    }
});

new Chart(document.getElementById('threadChart'), {
    type: 'pie',
    data: { 
        labels: threadLabels, 
        datasets: [{ 
            label: 'Replies', 
            data: threadCounts, 
            backgroundColor: ['#0288d1', '#7b1fa2', '#ff4081', '#00c853', '#ffd600'] 
        }] 
    },
    options: { 
        maintainAspectRatio: false,
        plugins: {
            legend: { labels: { color: '#e0e0e0' } }
        }
    }
});
</script>

</body>
</html>
