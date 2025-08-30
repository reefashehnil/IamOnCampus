<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header("Location: ../Login/login.php");
    exit;
}

$receipt = $_SESSION['receipt_data'] ?? null;
if (!$receipt) {
    header("Location: manage_users.php");
    exit;
}


date_default_timezone_set('Asia/Dhaka');


$rd = $receipt;
$rd['created_at'] = date('Y-m-d H:i:s'); 


unset($_SESSION['receipt_data']);

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a0b2e 0%, #0b1020 100%);
            color: #e8e8ff;
            font-family: 'Inter', sans-serif;
        }
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .receipt {
            background: #ffffff;
            color: #111111;
            border-radius: 16px;
            padding: 40px;
            max-width: 800px;
            margin: 40px auto;
            box-shadow: 0 8px  촬32px rgba(138, 43, 226, 0.2);
            border: 1px solid rgba(138, 43, 226, 0.3);
        }
        .receipt h3 {
            color: #a78bff;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .kv {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(138, 43, 226, 0.5);
            font-size: 1.1rem;
        }
        .kv:last-child {
            border-bottom: none;
        }
        . PolicyOptions: {
            color: #555555;
            font-weight: 500;
        }
        .kv div:last-child {
            color: #111111;
            font-weight: 600;
        }
        .alert {
            background: #e6e6fa;
            border: 1px solid #a78bff;
            color: #000000ff; 
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .btn-custom {
            background: linear-gradient(45deg, #8a2be2, #da70d6);
            border: none;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.4);
        }
        .btn-back {
            background: #2c2547;
            border: 1px solid #8a2be2;
        }
        .no-print {
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        .small {
            color: #555555;
            font-size: 0.9rem;
        }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; color: #000; }
            .receipt {
                background: #fff;
                color: #000;
                box-shadow: none;
                border: none;
                margin: 0;
            }
            .kv { border-bottom: 1px dashed #ccc; }
            .kv div:first-child { color: #555; }
            .kv div:last-child { color: #000; }
            .alert { background: #fff; color: #000; border: 1px solid #000; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="no-print text-center">
        <a href="manage_users.php" class="btn btn-custom btn-back">Back</a>
        <button onclick="window.print()" class="btn btn-custom">Print / Save as PDF</button>
    </div>

    <div class="receipt">
        <h3 class="mb-4">IamOnCampus — User Account Receipt</h3>
        <p class="small text-muted">Generated at <?= e($rd['created_at']) ?> by Admin #<?= e($rd['admin_id']) ?></p>

        <h5 class="mt-4">User Details</h5>
        <div class="kv"><div>Full Name</div><div><strong><?= e($rd['full_name']) ?></strong></div></div>
        <div class="kv"><div>User ID</div><div>#<?= e($rd['user_id']) ?></div></div>
        <div class="kv"><div>Email</div><div><?= e($rd['email']) ?></div></div>
        <div class="kv"><div>Role</div><div><?= e($rd['role']) ?></div></div>
        <div class="kv"><div>Department</div><div><?= e($rd['dept']) ?></div></div>

        <h5 class="mt-4">Temporary Login Credentials</h5>
        <div class="alert">This password is temporary. User must log in and change it immediately.</div>
        <div class="kv"><div>Password</div><div><strong><?= e($rd['temp_password']) ?></strong></div></div>

        <p class="small text-muted mt-4">* This receipt is shown once and then deleted from the system.</p>
    </div>
</div>
</body>
</html>