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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Event | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css?v=1.0" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css?v=1.0" rel="stylesheet" />
    <style>
        body {
            background-color: #1a0d2b !important; /* Dark violet-black background */
            color: #e6e6fa !important; /* Light lavender text for readability */
        }
        .container {
            background-color: #2a1b3d !important; /* Slightly lighter dark shade */
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px !important;
        }
        .card {
            background-color: #3c2f5c !important; /* Dark purple card background */
            border-color: #5a4b7c !important; /* Medium purple border */
            color: #e6e6fa !important; /* Light lavender text */
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5) !important; /* Darker shadow */
        }
        h2 {
            color: #d8bfd8 !important; /* Thistle color for heading */
        }
        .bi {
            color: #e6e6fa !important; /* Light lavender for icons */
        }
        .form-label {
            color: #e6e6fa !important; /* Light lavender for labels */
        }
        .form-control {
            background-color: #3c2f5c !important; /* Dark purple input background */
            color: #e6e6fa !important; /* Light lavender text */
            border-color: #5a4b7c !important; /* Medium purple border */
        }
        .form-control::placeholder {
            color: #e6e6fa !important; /* Light lavender placeholder text (less bright) */
        }
        .form-control:focus {
            border-color: #9370db !important; /* Medium purple border on focus */
            box-shadow: 0 0 8px rgba(123, 104, 238, 0.3) !important; /* Lighter purple shadow */
        }
        .btn-primary {
            background-color: #6a5acd !important; /* Slate blue button */
            border-color: #6a5acd !important;
        }
        .btn-primary:hover {
            background-color: #483d8b !important; /* Darker slate blue on hover */
            border-color: #483d8b !important;
        }
        .alert-success {
            background-color: #4b3c7a !important; /* Violet success alert */
            color: #e6e6fa !important;
            border-color: #5a4b7c !important;
        }
        .alert-danger {
            background-color: #5c2f5c !important; /* Dark purple danger alert */
            color: #e6e6fa !important;
            border-color: #7a4b7c !important;
        }
        /* No custom styles for btn-secondary to keep default Bootstrap styling */
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-warning mb-3">
        Back to Dashboard
    </a>
    <div class="card shadow p-4">
        <h2><i class="bi bi-calendar-check"></i> Register for Event</h2>
        <?php if (!empty($successMsg)): ?>
            <div class="alert alert-success"><?= $successMsg ?></div>
        <?php elseif (!empty($errorMsg)): ?>
            <div class="alert alert-danger"><?= $errorMsg ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="Please enter your phone number" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-circle"></i> Register
            </button>
        </form>
    </div>
</div>
</body>
</html>