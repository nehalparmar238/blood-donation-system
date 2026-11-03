<?php
session_start();
require 'db.php';

// Handle Blood Request Submission
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_blood'])) {
    $patient_name = trim($_POST['patient_name']);
    $blood_group = $_POST['blood_group'];
    $units = (int)$_POST['units'];
    $hospital = trim($_POST['hospital']);
    $city = trim($_POST['city']);
    $contact_person = trim($_POST['contact_person']);
    $phone = trim($_POST['phone']);
    $urgency = $_POST['urgency'];

    $stmt = $pdo->prepare("INSERT INTO blood_requests (patient_name, blood_group, units, hospital, city, contact_person, phone, urgency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$patient_name, $blood_group, $units, $hospital, $city, $contact_person, $phone, $urgency]);
    $msg = "Blood request broadcasted successfully! Nearby donors will be notified.";
}

// Fetch Blood Requests
$requests = $pdo->query("SELECT * FROM blood_requests WHERE status='Pending' ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Fetch Upcoming Camps
$camps = $pdo->query("SELECT * FROM camps WHERE status='Upcoming' ORDER BY camp_date ASC LIMIT 3")->fetchAll();

// Donor Search Filter
$search_bg = $_GET['blood_group'] ?? '';
$search_city = $_GET['city'] ?? '';
$donors = [];

if ($search_bg || $search_city) {
    $query = "SELECT name, blood_group, phone, city, last_donated FROM users WHERE is_admin = 0";
    $params = [];
    if ($search_bg) {
        $query .= " AND blood_group = ?";
        $params[] = $search_bg;
    }
    if ($search_city) {
        $query .= " AND city LIKE ?";
        $params[] = "%$search_city%";
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $donors = $stmt->fetchAll();
} else {
    $donors = $pdo->query("SELECT name, blood_group, phone, city, last_donated FROM users WHERE is_admin = 0 ORDER BY id DESC LIMIT 6")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LifeLine - Blood Donation & Emergency Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section { background: linear-gradient(rgba(180,40,40,0.85), rgba(120,20,20,0.9)), url('images/hero_blood.jpg'); background-size: cover; color: white; padding: 90px 0; }
        .stat-card { border-left: 4px solid #dc3545; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-danger sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="fa-solid fa-droplet text-white me-2"></i>LifeLine BloodBank</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="#search-donors">Find Donors</a></li>
                <li class="nav-item"><a class="nav-link" href="#blood-requests">Emergency Requests</a></li>
                <li class="nav-item"><a class="nav-link" href="#camps">Donation Camps</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle btn btn-light text-danger px-3" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($_SESSION['is_admin'] == 1): ?>
                                <li><a class="dropdown-item" href="admin.php"><i class="fa-solid fa-gauge me-2"></i>Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-2"><a class="btn btn-light text-danger fw-bold px-3" href="login.php">Login</a></li>
                    <li class="nav-item ms-2"><a class="btn btn-outline-light px-3" href="register.php">Register Donor</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero Section -->
<header class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Donate Blood, Save a Life</h1>
        <p class="lead mb-4">Every drop counts. Connect instantly with blood donors and emergency requests in your city.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#search-donors" class="btn btn-light btn-lg text-danger fw-bold px-4 shadow">Find Blood Donors</a>
            <button class="btn btn-outline-light btn-lg px-4" data-bs-toggle="modal" data-bs-target="#requestModal">Request Blood Now</button>
        </div>
    </div>
</header>

<!-- Alert Message -->
<?php if ($msg): ?>
<div class="container mt-4">
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Statistics Bar -->
<section class="container my-5">
    <div class="row text-center g-4">
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3 bg-white">
                <h3 class="text-danger fw-bold"><i class="fa-solid fa-users me-2"></i>1,250+</h3>
                <p class="text-muted mb-0">Registered Donors</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3 bg-white">
                <h3 class="text-danger fw-bold"><i class="fa-solid fa-hospital me-2"></i>45+</h3>
                <p class="text-muted mb-0">Partner Hospitals</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3 bg-white">
                <h3 class="text-danger fw-bold"><i class="fa-solid fa-hand-holding-medical me-2"></i>850+</h3>
                <p class="text-muted mb-0">Lives Saved</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card shadow-sm p-3 bg-white">
                <h3 class="text-danger fw-bold"><i class="fa-solid fa-calendar-days me-2"></i>12+</h3>
                <p class="text-muted mb-0">Upcoming Camps</p>
            </div>
        </div>
    </div>
</section>

<!-- Search Donors Section -->
<section id="search-donors" class="container my-5">
    <div class="card shadow-sm p-4 border-0">
        <h3 class="mb-4 text-danger fw-bold"><i class="fa-solid fa-magnifying-glass me-2"></i>Search Blood Donors</h3>
        <form method="GET" action="index.php" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">All Blood Groups</option>
                    <?php foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg): ?>
                        <option value="<?= $bg ?>" <?= $search_bg === $bg ? 'selected' : '' ?>><?= $bg ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-bold">City / Location</label>
                <input type="text" name="city" class="form-control" placeholder="e.g. Ahmedabad, Surat" value="<?= htmlspecialchars($search_city) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-danger w-100 fw-bold"><i class="fa-solid fa-search me-1"></i> Search</button>
            </div>
        </form>

        <div class="row mt-4 g-4">
            <?php if (empty($donors)): ?>
                <div class="col-12"><div class="alert alert-warning">No donors found matching your criteria.</div></div>
            <?php else: ?>
                <?php foreach($donors as $donor): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="fw-bold mb-0"><?= htmlspecialchars($donor['name']) ?></h5>
                                <span class="badge bg-danger fs-6 px-3 py-2 rounded-pill"><?= $donor['blood_group'] ?></span>
                            </div>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1 text-danger"></i> <?= htmlspecialchars($donor['city']) ?></p>
                            <p class="text-muted small mb-3"><i class="fa-solid fa-clock-rotate-left me-1 text-secondary"></i> Last Donated: <?= $donor['last_donated'] ? $donor['last_donated'] : 'Never / Eligible' ?></p>
                            <a href="tel:<?= $donor['phone'] ?>" class="mt-auto btn btn-outline-danger btn-sm w-100 fw-bold"><i class="fa-solid fa-phone me-1"></i> Call <?= htmlspecialchars($donor['phone']) ?></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Emergency Requests Section -->
<section id="blood-requests" class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-danger fw-bold mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>Emergency Blood Requests</h3>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#requestModal"><i class="fa-solid fa-plus me-1"></i> Raise Request</button>
    </div>
    <div class="row g-4">
        <?php foreach($requests as $req): ?>
            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm border-top border-danger border-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-<?= $req['urgency'] == 'Critical' ? 'dark' : 'warning text-dark' ?>"><?= $req['urgency'] ?></span>
                            <span class="badge bg-danger fs-6"><?= $req['blood_group'] ?> Required</span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Patient: <?= htmlspecialchars($req['patient_name']) ?></h5>
                        <p class="small text-muted mb-2"><i class="fa-solid fa-hospital me-1"></i> <?= htmlspecialchars($req['hospital']) ?>, <?= htmlspecialchars($req['city']) ?></p>
                        <p class="small mb-2"><strong>Units:</strong> <?= $req['units'] ?> Unit(s)</p>
                        <p class="small text-secondary mb-3"><i class="fa-solid fa-user-shield me-1"></i> Contact: <?= htmlspecialchars($req['contact_person']) ?></p>
                        <a href="tel:<?= $req['phone'] ?>" class="btn btn-danger btn-sm w-100 fw-bold"><i class="fa-solid fa-phone me-1"></i> Call Contact: <?= htmlspecialchars($req['phone']) ?></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Upcoming Camps Section -->
<section id="camps" class="container my-5">
    <h3 class="text-danger fw-bold mb-4"><i class="fa-solid fa-tent me-2"></i>Upcoming Blood Donation Camps</h3>
    <div class="row g-4">
        <?php foreach($camps as $camp): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="images/camp.jpg" class="card-img-top" style="height: 160px; object-fit: cover;" alt="Camp">
                    <div class="card-body">
                        <h5 class="fw-bold"><?= htmlspecialchars($camp['camp_name']) ?></h5>
                        <p class="text-muted small mb-1"><i class="fa-solid fa-building me-1"></i> Organizer: <?= htmlspecialchars($camp['organizer']) ?></p>
                        <p class="text-muted small mb-1"><i class="fa-solid fa-location-dot me-1"></i> <?= htmlspecialchars($camp['location']) ?></p>
                        <p class="text-danger small fw-bold mb-3"><i class="fa-solid fa-calendar me-1"></i> <?= $camp['camp_date'] ?> (<?= $camp['start_time'] ?> - <?= $camp['end_time'] ?>)</p>
                        <button class="btn btn-outline-danger btn-sm w-100" data-bs-toggle="modal" data-bs-target="#registerCampModal">Register for Camp</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Request Blood Modal -->
<div class="modal fade" id="requestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fa-solid fa-droplet me-2"></i>Request Blood</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" required>
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
                            <label class="form-label">Units Required</label>
                            <input type="number" name="units" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hospital Name</label>
                        <input type="text" name="hospital" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Person</label>
                            <input type="text" name="contact_person" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Urgency Level</label>
                        <select name="urgency" class="form-select">
                            <option value="Normal">Normal</option>
                            <option value="Urgent" selected>Urgent</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="request_blood" class="btn btn-danger">Broadcast Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
        <p class="mb-1">&copy; 2026 LifeLine BloodBank & Emergency Network. All rights reserved.</p>
        <p class="small text-muted">Saving lives through community connectivity.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
