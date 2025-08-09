<?php
session_start();
require '../Connection/db_connect.php';

if ($_SESSION['role'] !== 'Student') {
    die("Only students can register for events.");
}

$dashboardLink = "../Login/dashboard.php";

if (isset($_GET['event_id'])) {
    $event_id = intval($_GET['event_id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $phone = trim($_POST['phone']);

        if (!empty($phone)) {
            // Check if already registered
            $check = $conn->prepare("
                SELECT r.Event_id
                FROM event_registration r
                JOIN event_participants p ON r.Participant_id = p.Participant_id
                WHERE r.Event_id = ? AND p.User_id = ?
            ");
            $check->bind_param("ii", $event_id, $_SESSION['user_id']);
            $check->execute();
            $check_result = $check->get_result();

            if ($check_result->num_rows > 0) {
                $errorMsg = "⚠ You are already registered for this event.";
            } else {
                // Insert participant record
                $stmt = $conn->prepare("INSERT INTO event_participants (Phone, User_id) VALUES (?, ?)");
                $stmt->bind_param("si", $phone, $_SESSION['user_id']);
                $stmt->execute();
                $participant_id = $conn->insert_id;

                // Register participant to event
                $stmt = $conn->prepare("INSERT INTO event_registration (Event_id, Participant_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $event_id, $participant_id);

                if ($stmt->execute()) {
                    $successMsg = "Successfully registered for the event.";
                } else {
                    $errorMsg = "Error: " . $conn->error;
                }
            }
        } else {
            $errorMsg = "⚠ Please enter your phone number.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register for Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>
    <div class="card shadow p-4">
        <h2 class="text-primary"><i class="bi bi-calendar-check"></i> Register for Event</h2>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= $successMsg ?></div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= $errorMsg ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="Enter your phone number" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Register
            </button>
        </form>
    </div>
</div>
</body>
</html>
