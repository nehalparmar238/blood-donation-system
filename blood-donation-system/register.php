<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $blood_group = $_POST['blood_group'];
    $phone = trim($_POST['phone']);
    $city = trim($_POST['city']);
    $address = trim($_POST['address']);
    $last_donated = !empty($_POST['last_donated']) ? $_POST['last_donated'] : NULL;

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error = "Email is already registered.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, blood_group, phone, city, address, last_donated) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $blood_group, $phone, $city, $address, $last_donated]);
        $success = "Registration successful! You can now <a href='login.php'>login</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Donor Registration - LifeLine</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width: 600px;">
    <div class="card shadow-sm p-4 border-0">
        <div class="text-center mb-4">
            <i class="fa-solid fa-user-plus text-danger fs-1 mb-2"></i>
            <h3 class="fw-bold">Donor Registration</h3>
            <p class="text-muted small">Join our community of life savers</p>
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select" required>
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last Donated Date (Optional)</label>
                    <input type="date" name="last_donated" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Full Address</label>
                <textarea name="address" class="form-control" rows="2"></textarea>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold py-2 mb-3">Register as Donor</button>
            <p class="text-center small text-muted">Already have an account? <a href="login.php" class="text-danger fw-bold">Login</a></p>
            <div class="text-center mt-2">
                <a href="index.php" class="text-decoration-none small text-secondary">&larr; Back to Home</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
