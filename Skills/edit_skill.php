<?php
session_start();
include '../Connection/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$skill_id = $_GET['id'] ?? null;
if (!$skill_id) {
    header("Location: view_skill.php");
    exit;
}


$stmt = $conn->prepare("SELECT * FROM Skills WHERE Skill_id = ? AND User_id = ?");
$stmt->bind_param("ii", $skill_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  
    header("Location: view_skill.php");
    exit;
}

$skill = $result->fetch_assoc();

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $skill_name = trim($_POST['skill_name']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $mode = $_POST['mode'];

    if ($skill_name && $mode) {
        $stmt = $conn->prepare("UPDATE Skills SET Skill_name = ?, Skill_description = ?, Availability_time = ?, Mode = ? WHERE Skill_id = ? AND User_id = ?");
        $stmt->bind_param("ssssii", $skill_name, $description, $availability, $mode, $skill_id, $_SESSION['user_id']);

        if ($stmt->execute()) {
            $success = "Skill updated successfully.";
         
            $skill['Skill_name'] = $skill_name;
            $skill['Skill_description'] = $description;
            $skill['Availability_time'] = $availability;
            $skill['Mode'] = $mode;
        } else {
            $error = "Error updating skill: " . $conn->error;
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Edit Skill | IamOnCampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        body {
            background: linear-gradient(135deg, #1a1a1a, #2a1a3a); 
            font-family: Arial;
            color: #fff; 
        }
        .container {
            max-width: 600px; 
            margin: 0 auto; 
            padding: 15px;
            background: #2c1e3f; 
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.5);
        }
        h2 {
            color: #fff; 
        }
        .form-control, .form-select {
            background: #3a2a5a; 
            border: 1px solid #4a3066; 
            color: #fff; 
            padding-right: 30px; 
        }
        .form-control::placeholder {
            color: #ccc; 
        }
        .form-label {
            color: #fff; 
        }
        .btn-primary {
            background: #4a3066;
            border: none;
            color: #fff; 
        }
        .btn-primary:hover {
            background: #5a4080; 
            color: #fff;
        }
        .alert-success {
            background: #4a3066; 
            color: #fff; 
            border: 1px solid #5a4080; 
        }
        .alert-danger {
            background: #ff6666;
            color: #fff;
            border: 1px solid #4a3066; 
        }
        
        .select-wrapper {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .select-wrapper::after {
            content: '\25B4'; 
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            color: #ccc; 
            font-size: 0.8rem;
            pointer-events: none; 
        }
        .form-select {
            appearance: none; 
            -webkit-appearance: none;
            -moz-appearance: none;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Edit Skill</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="skill_name" class="form-label">Skill Name *</label>
            <div class="select-wrapper">
                <select id="skill_name" name="skill_name" class="form-select" required>
                    <?php
                    $skills = [
                        "Public Speaking",
                        "Creative Writing",
                        "Mathematical Problem Solving",
                        "Critical Thinking",
                        "Research & Report Writing",
                        "Programming (e.g., Python, C++)",
                        "Graphic Design (e.g., Photoshop, Canva)",
                        "Video Editing (e.g., Premiere Pro, CapCut)",
                        "Web Development (HTML, CSS, JS)",
                        "MS Excel / Google Sheets (Data Handling)",
                        "English Speaking Practice",
                        "Translation (e.g., Bengali ↔ English)",
                        "Academic Presentation Preparation",
                        "Resume/CV Writing & Review",
                        "Debate Coaching",
                        "Photography Basics",
                        "Sketching & Drawing",
                        "Playing a Musical Instrument (e.g., Guitar, Piano)",
                        "DIY Crafts",
                        "Content Creation (YouTube/Instagram Reels)"
                    ];

                    foreach ($skills as $skill_option) {
                        $selected = ($skill_option === $skill['Skill_name']) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($skill_option) . "\" $selected>$skill_option</option>";
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($skill['Skill_description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="availability" class="form-label">Availability Time</label>
            <input type="text" name="availability" id="availability" class="form-control"
                   value="<?= htmlspecialchars($skill['Availability_time']) ?>">
        </div>
        <div class="mb-3">
            <label for="mode" class="form-label">Mode *</label>
            <div class="select-wrapper">
                <select name="mode" id="mode" class="form-select" required>
                    <option value="">Select Mode</option>
                    <option value="online" <?= $skill['Mode'] === 'online' ? 'selected' : '' ?>>Online</option>
                    <option value="offline" <?= $skill['Mode'] === 'offline' ? 'selected' : '' ?>>Offline</option>
                    <option value="both" <?= $skill['Mode'] === 'both' ? 'selected' : '' ?>>Both</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Update Skill</button>
    </form>

    <a href="my_skills.php" class="btn btn-warning mt-3">Back to Skills</a>
</div>
</body>
</html>