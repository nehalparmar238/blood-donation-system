<?php
session_start();
require 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: login.php");
    exit;
}

// Handle Status Updates / Deletions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] == 'fulfill_req') {
        $stmt = $pdo->prepare("UPDATE blood_requests SET status = 'Fulfilled' WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] == 'delete_req') {
        $stmt = $pdo->prepare("DELETE FROM blood_requests WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] == 'delete_user') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
        $stmt->execute([$id]);
    }
    header("Location: admin.php");
    exit;
}

// Fetch stats
$total_donors = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();
$total_requests = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
$pending_requests = $pdo->query("SELECT COUNT(*) FROM blood_requests WHERE status='Pending'")->fetchColumn();
$total_camps = $pdo->query("SELECT COUNT(*) FROM camps")->fetchColumn();

// Fetch lists
$users = $pdo->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY id DESC")->fetchAll();
$requests = $pdo->query("SELECT * FROM blood_requests ORDER BY created_at DESC")->fetchAll();
$camps = $pdo->query("SELECT * FROM camps ORDER BY camp_date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - LifeLine BloodBank</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="admin.php"><i class="fa-solid fa-gauge text-danger me-2"></i>LifeLine Admin Panel</a>
        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">Logged in as <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong></span>
            <a href="index.php" class="btn btn-outline-light btn-sm">View Website</a>
            <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-danger border-4 p-3 bg-white">
                <h6 class="text-muted">Total Donors</h6>
                <h3 class="fw-bold text-danger mb-0"><?= $total_donors ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-warning border-4 p-3 bg-white">
                <h6 class="text-muted">Pending Requests</h6>
                <h3 class="fw-bold text-warning mb-0"><?= $pending_requests ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-success border-4 p-3 bg-white">
                <h6 class="text-muted">Total Blood Requests</h6>
                <h3 class="fw-bold text-success mb-0"><?= $total_requests ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 border-start border-primary border-4 p-3 bg-white">
                <h6 class="text-muted">Donation Camps</h6>
                <h3 class="fw-bold text-primary mb-0"><?= $total_camps ?></h3>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active fw-bold text-danger" id="requests-tab" data-bs-toggle="tab" data-bs-target="#requests" type="button">Emergency Requests</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-danger" id="donors-tab" data-bs-toggle="tab" data-bs-target="#donors" type="button">Registered Donors</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-danger" id="camps-tab" data-bs-toggle="tab" data-bs-target="#camps" type="button">Donation Camps</button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content">
        <!-- Blood Requests Tab -->
        <div class="tab-pane fade show active" id="requests">
            <div class="card shadow-sm border-0 p-3">
                <h4 class="mb-3 fw-bold text-dark">Manage Blood Requests</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Patient</th>
                                <th>Blood Group</th>
                                <th>Units</th>
                                <th>Hospital / City</th>
                                <th>Contact</th>
                                <th>Urgency</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($requests as $r): ?>
                            <tr>
                                #<?= $r['id'] ?>
                                <td>#<?= $r['id'] ?></td>
                                <td><?= htmlspecialchars($r['patient_name']) ?></td>
                                <td><span class="badge bg-danger"><?= $r['blood_group'] ?></span></td>
                                <td><?= $r['units'] ?></td>
                                <td><?= htmlspecialchars($r['hospital']) ?><br><small class="text-muted"><?= htmlspecialchars($r['city']) ?></small></td>
                                <td><?= htmlspecialchars($r['contact_person']) ?><br><small><?= htmlspecialchars($r['phone']) ?></small></td>
                                <td><span class="badge bg-<?= $r['urgency'] == 'Critical' ? 'dark' : 'warning text-dark' ?>"><?= $r['urgency'] ?></span></td>
                                <td><span class="badge bg-<?= $r['status'] == 'Fulfilled' ? 'success' : 'secondary' ?>"><?= $r['status'] ?></span></td>
                                <td>
                                    <?php if($r['status'] == 'Pending'): ?>
                                        <a href="admin.php?action=fulfill_req&id=<?= $r['id'] ?>" class="btn btn-success btn-sm mb-1" title="Mark Fulfilled"><i class="fa-solid fa-check"></i></a>
                                    <?php endif; ?>
                                    <a href="admin.php?action=delete_req&id=<?= $r['id'] ?>" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Delete this request?');" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Donors Tab -->
        <div class="tab-pane fade" id="donors">
            <div class="card shadow-sm border-0 p-3">
                <h4 class="mb-3 fw-bold text-dark">Registered Donors Directory</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Blood Group</th>
                                <th>City & Phone</th>
                                <th>Last Donated</th>
                                <th>Registered At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td>#<?= $u['id'] ?></td>
                                <td><?= htmlspecialchars($u['name']) ?><br><small class="text-muted"><?= htmlspecialchars($u['email']) ?></small></td>
                                <td><span class="badge bg-danger"><?= $u['blood_group'] ?></span></td>
                                <td><?= htmlspecialchars($u['city']) ?><br><small><?= htmlspecialchars($u['phone']) ?></small></td>
                                <td><?= $u['last_donated'] ? $u['last_donated'] : 'N/A' ?></td>
                                <td><?= $u['created_at'] ?></td>
                                <td>
                                    <a href="admin.php?action=delete_user&id=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this donor?');"><i class="fa-solid fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Camps Tab -->
        <div class="tab-pane fade" id="camps">
            <div class="card shadow-sm border-0 p-3">
                <h4 class="mb-3 fw-bold text-dark">Donation Camps Management</h4>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Camp Name</th>
                                <th>Organizer</th>
                                <th>Location</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($camps as $c): ?>
                            <tr>
                                <td><?= htmlspecialchars($c['camp_name']) ?></td>
                                <td><?= htmlspecialchars($c['organizer']) ?></td>
                                <td><?= htmlspecialchars($c['location']) ?></td>
                                <td><?= $c['camp_date'] ?> (<?= $c['start_time'] ?> - <?= $c['end_time'] ?>)</td>
                                <td><span class="badge bg-primary"><?= $c['status'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
