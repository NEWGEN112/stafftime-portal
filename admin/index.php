<?php
// StaffTime - Admin Dashboard (Starting Point)
session_start();

// Temporary demo mode (remove later when real login is ready)
$school_name = "Glory Secondary School";
$school_code = "ST-2026-0001";
$admin_name  = "Admin";

// Demo stats (will come from database later)
$present = 42;
$absent  = 5;
$late    = 3;
$leave   = 2;
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
            <small class="text-muted">School Code: <?php echo $school_code; ?> • Welcome, <?php echo $admin_name; ?></small>
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

        <!-- Today's Attendance Preview -->
        <div class="card">
          <div class="card-header bg-white d-flex justify-content-between">
            <strong>Today's Attendance</strong>
            <a href="attendance.php" class="btn btn-sm btn-outline-primary">View Full</a>
          </div>
          <div class="card-body p-0">
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
                  <tr>
                    <td>Adebayo Johnson</td>
                    <td>07:38 AM</td>
                    <td><span class="badge bg-success">Present</span></td>
                    <td>Mathematics</td>
                  </tr>
                  <tr>
                    <td>Fatima Okonkwo</td>
                    <td>07:51 AM</td>
                    <td><span class="badge bg-warning text-dark">Late</span></td>
                    <td>English</td>
                  </tr>
                  <tr>
                    <td>Chidi Okeke</td>
                    <td>—</td>
                    <td><span class="badge bg-danger">Absent</span></td>
                    <td>Science</td>
                  </tr>
                  <tr>
                    <td>Sade Adebayo</td>
                    <td>—</td>
                    <td><span class="badge bg-info">Leave</span></td>
                    <td>Admin Staff</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
