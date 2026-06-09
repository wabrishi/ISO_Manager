<?php
session_start();
if(isset($_SESSION['admin_id'])){
    header("Location: index.php");
    exit();
}

require_once '../config/database.php';
require_once '../models/AdminUser.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "CSRF Token Validation Failed";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $admin = new AdminUser($db);

        if($admin->login($_POST['username'], $_POST['password'])){
            $_SESSION['admin_id'] = $admin->id;
            $_SESSION['admin_username'] = $admin->username;
            $_SESSION['admin_role'] = $admin->role;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    }
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ISO Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; }
        .login-box { width: 400px; margin: 100px auto; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="text-center mb-4">ISO Manager Admin</h3>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST" action="login.php">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
