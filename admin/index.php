<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_name = $user['school_name'] ?? 'My School';
$school_code = $_SESSION['school_code'] ?? 'N/A';
$admin_name  = $user['full_name'] ?? 'Admin';
$school_id   = $user['school_id'];
$today       = date('Y-m-d');

// Default stats
$present = 0;
$late    = 0;
$absent  = 0;
$leave   = 0;
$today_list = [];

try {
    $pdo = getDB();

    // Count today's attendance by status
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

    // Get list of staff who checked in today
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
    // Keep zeros if database fails
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
    .sidebar {
      min-height: 100vh;
      background: #0d6efd;
      color: white;
    }
    .sidebar a {
      color: rgba(255,255,255,0.85);
      text-decoration: none;
      display: block;
      padding: 12px 20px;
      border-radius: 8px;
      margin: 4px 10px;
    }
    .sidebar a:hover, .sidebar a.active {
      background: rgba(255,255,255,0.15);
      color: white;
    }
    .stat-card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .stat-card .number {
      font-size: 2rem;
      font-weight: 700;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <div class="col-md-3 col-lg-2 sidebar p-0">
        <div class="p-3 text-center border-bottom border-light border-opacity-25">
          <h4 class="mb-0">StaffTime</h4>
          <small>Admin Portal</small>
        </div>
        <nav class="mt-3">
          <a href="index.php" class="active">🏠 Dashboard</a>
          <a href="staff.php">👥 Manage Staff</a>
          <a href="attendance.php">📋 Attendance</a>
          <a href="reports.php">📄 Reports (PDF)</a>
          <a href="calendar.php">📅 Academic Calendar</a>
          <a href="leaves.php">🏖️ Leave Requests</a>
          <a href="settings.php">⚙️ Settings</a>
          <a href="../public/logout.php">🚪 Logout</a>
        </nav>
      </div>

      <!-- Main Content -->
      <div class="col-md-9 col-lg-10 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h3 class="mb-0"><?php echo htmlspecialchars($school_name); ?></h3>
            <small class="text-muted">School Code: <?php echo htmlspecialchars($school_code); ?> • Welcome, <?php echo htmlspecialchars($admin_name); ?></small>
          </div>
          <span class="badge bg-primary">Today: <?php echo date('D, d M Y'); ?></span>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
          <div class="col-6 col-md-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="number text-success"><?php echo $present; ?></div>
                <div class="text-muted">Present Today</div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="number text-danger"><?php echo $absent; ?></div>
                <div class="text-muted">Absent Today</div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="number text-warning"><?php echo $late; ?></div>
                <div class="text-muted">Late Today</div>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3">
            <div class="card stat-card">
              <div class="card-body text-center">
                <div class="number text-info"><?php echo $leave; ?></div>
                <div class="text-muted">On Leave</div>
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
            <a href="staff-add.php" class="btn btn-primary me-2 mb-2">+ Add New Staff</a>
            <a href="staff.php" class="btn btn-outline-primary me-2 mb-2">View All Staff</a>
            <a href="reports.php" class="btn btn-outline-primary me-2 mb-2">Generate PDF Report</a>
            <a href="calendar.php" class="btn btn-outline-primary mb-2">Set Terms & Session</a>
          </div>
        </div>

        <!-- Today's Attendance -->
        <div class="card">
          <div class="card-header bg-white d-flex justify-content-between">
            <strong>Today's Attendance</strong>
            <a href="attendance.php" class="btn btn-sm btn-outline-primary">View Full</a>
          </div>
          <div class="card-body p-0">
            <?php if (empty($today_list)): ?>
              <div class="text-center py-4 text-muted">
                No attendance records yet today.
              </div>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Staff Name</th>
                      <th>Time In</th>
                      <th>Status</th>
                      <th>Department</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($today_list as $row): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td>
                          <?php 
                            echo $row['check_in_time'] 
                              ? date('h:i A', strtotime($row['check_in_time'])) 
                              : '—'; 
                          ?>
                        </td>
                        <td>
                          <?php
                            $status = $row['status'];
                            $badge = match($status) {
                              'present' => 'success',
                              'late'    => 'warning',
                              'absent'  => 'danger',
                              'leave'   => 'info',
                              default   => 'secondary'
                            };
                          ?>
                          <span class="badge bg-<?php echo $badge; ?>">
                            <?php echo ucfirst($status); ?>
                          </span>
                        </td>
                        <td><?php echo htmlspecialchars($row['department'] ?: '—'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
