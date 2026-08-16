<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id   = $user['school_id'];
$school_name = $user['school_name'] ?? 'School';

$sessions = [];
$terms = [];
$report_data = [];
$selected_session = null;
$selected_term = null;
$error = '';
$show_report = false;

try {
    $pdo = getDB();

    // Load sessions
    $stmt = $pdo->prepare("SELECT * FROM sessions WHERE school_id = ? ORDER BY session_name DESC");
    $stmt->execute([$school_id]);
    $sessions = $stmt->fetchAll();

    // Handle form submit
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $session_id = intval($_POST['session_id'] ?? 0);
        $term_id    = intval($_POST['term_id'] ?? 0);

        // Get session
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND school_id = ?");
        $stmt->execute([$session_id, $school_id]);
        $selected_session = $stmt->fetch();

        if ($term_id > 0) {
            // Specific term
            $stmt = $pdo->prepare("SELECT * FROM terms WHERE id = ? AND session_id = ?");
            $stmt->execute([$term_id, $session_id]);
            $selected_term = $stmt->fetch();
            $start_date = $selected_term['start_date'];
            $end_date   = $selected_term['end_date'];
            $report_title = $selected_term['term_name'] . ' - ' . $selected_session['session_name'];
        } else {
            // Full session
            $start_date = $selected_session['start_date'];
            $end_date   = $selected_session['end_date'];
            $report_title = 'Full Session - ' . $selected_session['session_name'];
        }

        if ($start_date && $end_date) {
            // Get all active staff
            $staffStmt = $pdo->prepare("
                SELECT id, full_name, staff_id_number, department 
                FROM users 
                WHERE school_id = ? AND role = 'staff' AND is_active = 1
                ORDER BY full_name
            ");
            $staffStmt->execute([$school_id]);
            $staff_list = $staffStmt->fetchAll();

            // Get attendance summary for the period
            foreach ($staff_list as $staff) {
                $attStmt = $pdo->prepare("
                    SELECT status, COUNT(*) as total
                    FROM attendances
                    WHERE user_id = ? AND school_id = ? 
                    AND attendance_date BETWEEN ? AND ?
                    GROUP BY status
                ");
                $attStmt->execute([$staff['id'], $school_id, $start_date, $end_date]);
                $counts = $attStmt->fetchAll();

                $present = 0; $late = 0; $absent = 0; $leave = 0;
                foreach ($counts as $c) {
                    if ($c['status'] === 'present') $present = $c['total'];
                    if ($c['status'] === 'late')    $late    = $c['total'];
                    if ($c['status'] === 'absent')  $absent  = $c['total'];
                    if ($c['status'] === 'leave')   $leave   = $c['total'];
                }

                $total_days = $present + $late + $absent + $leave;
                $percentage = $total_days > 0 ? round((($present + $late) / $total_days) * 100, 1) : 0;

                $report_data[] = [
                    'name'       => $staff['full_name'],
                    'staff_id'   => $staff['staff_id_number'],
                    'department' => $staff['department'],
                    'present'    => $present,
                    'late'       => $late,
                    'absent'     => $absent,
                    'leave'      => $leave,
                    'total'      => $total_days,
                    'percentage' => $percentage
                ];
            }
            $show_report = true;
        } else {
            $error = 'Please set the start and end dates for the selected session/term first.';
        }
    }

    // Load terms when session is selected (for dropdown)
    if (!empty($sessions)) {
        $first_session_id = $sessions[0]['id'];
        $stmt = $pdo->prepare("SELECT * FROM terms WHERE session_id = ? ORDER BY term_number");
        $stmt->execute([$first_session_id]);
        $terms = $stmt->fetchAll();
    }

} catch (Exception $e) {
    $error = 'Could not generate report.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance Reports - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    @media print {
      .no-print { display: none !important; }
      body { background: white; }
      .card { border: none; box-shadow: none; }
    }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
      <h3>Attendance Reports</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger no-print"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Report Form -->
    <div class="card mb-4 no-print">
      <div class="card-body">
        <form method="POST">
          <div class="row g-3">
            <div class="col-md-5">
              <label class="form-label">Select Session</label>
              <select name="session_id" class="form-select" required>
                <?php foreach ($sessions as $s): ?>
                  <option value="<?php echo $s['id']; ?>">
                    <?php echo htmlspecialchars($s['session_name']); ?>
                    <?php echo $s['is_current'] ? '(Current)' : ''; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-5">
              <label class="form-label">Select Term</label>
              <select name="term_id" class="form-select">
                <option value="0">Full Session (All Terms)</option>
                <?php foreach ($terms as $t): ?>
                  <option value="<?php echo $t['id']; ?>">
                    <?php echo htmlspecialchars($t['term_name']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Generate</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Report Result -->
    <?php if ($show_report): ?>
      <div class="card">
        <div class="card-body">
          
          <!-- Header for printing -->
          <div class="text-center mb-4">
            <h4><?php echo htmlspecialchars($school_name); ?></h4>
            <h5>Staff Attendance Report</h5>
            <p class="mb-1"><strong><?php echo htmlspecialchars($report_title); ?></strong></p>
            <p class="text-muted">
              Period: <?php echo date('d M Y', strtotime($start_date)); ?> 
              – <?php echo date('d M Y', strtotime($end_date)); ?>
            </p>
            <p class="text-muted small">Generated on <?php echo date('d M Y, h:i A'); ?></p>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered table-sm">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Staff Name</th>
                  <th>Staff ID</th>
                  <th>Present</th>
                  <th>Late</th>
                  <th>Absent</th>
                  <th>Leave</th>
                  <th>Total Days</th>
                  <th>Attendance %</th>
                </tr>
              </thead>
              <tbody>
                <?php $i = 1; foreach ($report_data as $row): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['staff_id'] ?: '—'); ?></td>
                    <td class="text-success"><?php echo $row['present']; ?></td>
                    <td class="text-warning"><?php echo $row['late']; ?></td>
                    <td class="text-danger"><?php echo $row['absent']; ?></td>
                    <td class="text-info"><?php echo $row['leave']; ?></td>
                    <td><?php echo $row['total']; ?></td>
                    <td><strong><?php echo $row['percentage']; ?>%</strong></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-5 pt-4">
            <div class="row">
              <div class="col-6">
                <p>_________________________</p>
                <p>Admin Signature</p>
              </div>
              <div class="col-6 text-end">
                <p>_________________________</p>
                <p>Principal Signature</p>
              </div>
            </div>
          </div>

          <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-success btn-lg">
              🖨️ Print / Save as PDF
            </button>
          </div>

        </div>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>
