<?php
session_start();
require 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['is_admin'] = $user['is_admin'];

        if ($user['is_admin'] == 1) {
            header("Location: admin.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - LifeLine BloodBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
<div class="card shadow-sm p-4 border-0" style="max-width: 400px; width: 100%;">
    <div class="text-center mb-4">
        <i class="fa-solid fa-droplet text-danger fs-1 mb-2"></i>
        <h3 class="fw-bold">Welcome Back</h3>
        <p class="text-muted small">Sign in to your LifeLine account</p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger py-2 small"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-danger w-100 fw-bold py-2 mb-3">Login</button>
        <p class="text-center small text-muted">Don't have an account? <a href="register.php" class="text-danger fw-bold">Register here</a></p>
        <div class="text-center mt-2">
            <a href="index.php" class="text-decoration-none small text-secondary">&larr; Back to Home</a>
        </div>
    </form>
</div>
</body>
</html>
