<?php
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ISO Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background-color: #343a40; color: white; }
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
        .content { padding: 20px; }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar col-md-2 p-0">
            <h4 class="text-center py-3 border-bottom border-secondary">ISO Manager</h4>
            <nav class="mt-3">
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                <a href="organizations.php"><i class="fas fa-building me-2"></i> Organizations</a>
                <a href="certificates.php"><i class="fas fa-certificate me-2"></i> Certificates</a>
                <a href="verification_logs.php"><i class="fas fa-history me-2"></i> Verification Logs</a>
                <a href="inquiries.php"><i class="fas fa-envelope me-2"></i> Inquiries</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
            </nav>
        </div>
        <div class="content col-md-10 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard</h2>
                <div>
                    <span>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                </div>
            </div>
