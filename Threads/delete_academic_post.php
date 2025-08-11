<?php
session_start();
include '../Connection/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') exit("Unauthorized");

$id = intval($_GET['Post_id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM academic_posts WHERE Post_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
header("Location: moderate_content.php");
