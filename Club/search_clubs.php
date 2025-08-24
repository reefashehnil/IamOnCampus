<?php
session_start();
require '../Connection/db_connect.php';

$dashboardLink = ($_SESSION['role'] === 'Admin') ? "../Login/admin_dashboard.php" : "../Login/dashboard.php";
$query = isset($_GET['q']) ? $_GET['q'] : '';

$stmt = $conn->prepare("SELECT * FROM club WHERE Club_name LIKE ?");
$searchTerm = "%$query%";
$stmt->bind_param("s", $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Clubs | IamOnCampus</title>
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
        h2 {
            color: #d8bfd8 !important; /* Thistle color for heading */
        }
        .bi {
            color: #e6e6fa !important; /* Light lavender for icons */
        }
        .form-control {
            background-color: #3c2f5c !important; /* Dark purple input background */
            color: #e6e6fa !important; /* Light lavender text */
            border-color: #5a4b7c !important; /* Medium purple border */
        }
        .form-control::placeholder {
            color: #e6e6fa !important; /* Light lavender placeholder text */
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
        .table {
            background-color: #3c2f5c !important; /* Dark purple table background */
            color: #e6e6fa !important; /* Light lavender text */
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #4b3c7a !important; /* Violet stripe background */
        }
        .table-hover tbody tr:hover {
            background-color: #5a4b7c !important; /* Medium purple on hover */
        }
        .table th,
        .table td {
            border-color: #5a4b7c !important; /* Medium purple borders */
        }
        .table thead th {
            background-color: #4b3c7a !important; /* Violet header background */
            color: #e6e6fa !important; /* Light lavender text */
        }
        /* No custom styles for btn-secondary to keep default Bootstrap styling */
    </style>
</head>
<body>
<div class="container mt-4">
    <a href="<?= $dashboardLink ?>" class="btn btn-secondary mb-3">
        Back to Dashboard
    </a>
    <h2 class="mb-3">Search Clubs</h2>
    <form method="GET" class="d-flex mb-3">
        <input type="text" name="q" class="form-control me-2" placeholder="Search clubs..." value="<?= htmlspecialchars($query) ?>">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-search"></i> Search
        </button>
    </form>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Club ID</th>
                <th>Club Name</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['Club_id'] ?></td>
                    <td><?= htmlspecialchars($row['Club_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>