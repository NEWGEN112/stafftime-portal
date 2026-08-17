<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_name = $user['school_name'] ?? 'My School';
$school_code = $_SESSION['school_code'] ?? 'N/A';
$admin_name  = $user['full_name'] ?? 'Admin';
$school_id   = $user['school_id'];
$today       = date('Y-m-d');

$present = 0;
$late    = 0;
$absent  = 0;
$leave   = 0;
$today_list = [];

try {
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as total 
        FROM attendances 
        WHERE school_id = ? AND attendance_date = ?
        GROUP BY status
    ");
    $stmt->execute([$school_id, $today]);
    $counts = $stmt->fetchAll();

    foreach ($counts as $row) {
        if ($row['status'] === 'present') $present = $row['total'];
        if ($row['status'] === 'late')    $late    = $row['total'];
        if ($row['status'] === 'absent')  $absent  = $row['total'];
        if ($row['status'] === 'leave')   $leave   = $row['total'];
    }

    $stmt = $pdo->prepare("
        SELECT u.full_name, u.department, a.check_in_time, a.status
        FROM attendances a
        JOIN users u ON a.user_id = u.id
        WHERE a.school_id = ? AND a.attendance_date = ?
        ORDER BY a.check_in_time ASC
    ");
    $stmt->execute([$school_id, $today]);
    $today_list = $stmt->fetchAll();

} catch (Exception $e) {
    // keep zeros
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .stat-card {
      border: none;
      border-radius: 14px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .stat-card .number {
      font-size: 1.8rem;
      font-weight: 700;
    }
    .nav-link-custom {
      color: #333;
      padding: 12px 16px;
      border-radius: 10px;
      display: block;
      text-decoration: none;
      margin-bottom: 4px;
    }
    .nav-link-custom:hover, .nav-link-custom.active {
      background: #0d6efd;
      color: white;
    }
    .offcanvas-body .nav-link-custom {
      font-size: 1.05rem;
    }
  </style>
</head>
<body>

  <!-- Top Navbar (Mobile Friendly) -->
  <nav class="navbar navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
      <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu">
        ☰ Menu
      </button>
      <span class="navbar-brand mb-0 h1">StaffTime</span>
      <span class="text-white small d-none d-sm-inline"><?php echo htmlspecialchars($admin_name); ?></span>
    </div>
  </nav>

  <!-- Offcanvas Sidebar -->
  <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
    <div class="offcanvas-header bg-primary text-white">
      <h5 class="offcanvas-title">StaffTime Admin</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <a href="index.php" class="nav-link-custom active">🏠 Dashboard</a>
      <a href="staff.php" class="nav-link-custom">👥 Manage Staff</a>
      <a href="attendance.php" class="nav-link-custom">📋 Attendance</a>
      <a href="reports.php" class="nav-link-custom">📄 Reports (PDF)</a>
      <a href="calendar.php" class="nav-link-custom">📅 Academic Calendar</a>
      <a href="leaves.php" class="nav-link-custom">🏖️ Leave Requests</a>
      <a href="settings.php" class="nav-link-custom">⚙️ Settings</a>
      <hr>
      <a href="../public/logout.php" class="nav-link-custom text-danger">🚪 Logout</a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container py-4">

    <div class="mb-4">
      <h4 class="mb-1"><?php echo htmlspecialchars($school_name); ?></h4>
      <small class="text-muted">
        Code: <?php echo htmlspecialchars($school_code); ?> • 
        <?php echo date('D, d M Y'); ?>
      </small>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-6">
        <div class="card stat-card">
          <div class="card-body text-center py-3">
            <div class="number text-success"><?php echo $present; ?></div>
            <div class="text-muted small">Present</div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card">
          <div class="card-body text-center py-3">
            <div class="number text-danger"><?php echo $absent; ?></div>
            <div class="text-muted small">Absent</div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card">
          <div class="card-body text-center py-3">
            <div class="number text-warning"><?php echo $late; ?></div>
            <div class="text-muted small">Late</div>
          </div>
        </div>
      </div>
      <div class="col-6">
        <div class="card stat-card">
          <div class="card-body text-center py-3">
            <div class="number text-info"><?php echo $leave; ?></div>
            <div class="text-muted small">On Leave</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>Quick Actions</strong>
      </div>
      <div class="card-body">
        <div class="d-grid gap-2">
          <a href="staff-add.php" class="btn btn-primary">+ Add New Staff</a>
          <a href="staff.php" class="btn btn-outline-primary">View All Staff</a>
          <a href="reports.php" class="btn btn-outline-primary">Generate PDF Report</a>
          <a href="calendar.php" class="btn btn-outline-primary">Set Terms & Session</a>
        </div>
      </div>
    </div>

    <!-- Today's Attendance -->
    <div class="card">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong>Today's Attendance</strong>
        <a href="attendance.php" class="btn btn-sm btn-outline-primary">View Full</a>
      </div>
      <div class="card-body p-0">
        <?php if (empty($today_list)): ?>
          <div class="text-center py-4 text-muted">
            No attendance records yet today.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($today_list as $row): ?>
              <?php
                $badge = match($row['status']) {
                  'present' => 'success',
                  'late'    => 'warning',
                  'absent'  => 'danger',
                  'leave'   => 'info',
                  default   => 'secondary'
                };
              ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                    <br>
                    <small class="text-muted">
                      <?php echo $row['check_in_time'] ? date('h:i A', strtotime($row['check_in_time'])) : '—'; ?>
                      • <?php echo htmlspecialchars($row['department'] ?: 'No dept'); ?>
                    </small>
                  </div>
                  <span class="badge bg-<?php echo $badge; ?> align-self-center">
                    <?php echo ucfirst($row['status']); ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
