<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

// Get selected date (default = today)
$selected_date = $_GET['date'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'all';

$attendance_list = [];
$error = '';

try {
    $pdo = getDB();

    // Get all active staff of this school
    $staffStmt = $pdo->prepare("
        SELECT id, full_name, staff_id_number, department, phone
        FROM users 
        WHERE school_id = ? AND role = 'staff' AND is_active = 1
        ORDER BY full_name ASC
    ");
    $staffStmt->execute([$school_id]);
    $all_staff = $staffStmt->fetchAll();

    // Get attendance records for the selected date
    $attStmt = $pdo->prepare("
        SELECT user_id, check_in_time, check_out_time, status
        FROM attendances 
        WHERE school_id = ? AND attendance_date = ?
    ");
    $attStmt->execute([$school_id, $selected_date]);
    $records = $attStmt->fetchAll();

    // Map records by user_id for easy lookup
    $attendance_map = [];
    foreach ($records as $rec) {
        $attendance_map[$rec['user_id']] = $rec;
    }

    // Build final list
    foreach ($all_staff as $staff) {
        $record = $attendance_map[$staff['id']] ?? null;

        if ($record) {
            $status = $record['status'];
            $check_in = $record['check_in_time'];
            $check_out = $record['check_out_time'];
        } else {
            // No record = Absent
            $status = 'absent';
            $check_in = null;
            $check_out = null;
        }

        // Apply status filter
        if ($status_filter !== 'all' && $status !== $status_filter) {
            continue;
        }

        $attendance_list[] = [
            'full_name'    => $staff['full_name'],
            'staff_id'     => $staff['staff_id_number'],
            'department'   => $staff['department'],
            'phone'        => $staff['phone'],
            'check_in'     => $check_in,
            'check_out'    => $check_out,
            'status'       => $status
        ];
    }

} catch (Exception $e) {
    $error = 'Could not load attendance data.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Attendance Records</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="GET" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Select Date</label>
            <input type="date" name="date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Filter by Status</label>
            <select name="status" class="form-select">
              <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All</option>
              <option value="present" <?php echo $status_filter === 'present' ? 'selected' : ''; ?>>Present</option>
              <option value="late" <?php echo $status_filter === 'late' ? 'selected' : ''; ?>>Late</option>
              <option value="absent" <?php echo $status_filter === 'absent' ? 'selected' : ''; ?>>Absent</option>
              <option value="leave" <?php echo $status_filter === 'leave' ? 'selected' : ''; ?>>Leave</option>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
          </div>
        </form>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Attendance Table -->
    <div class="card">
      <div class="card-header bg-white d-flex justify-content-between">
        <strong>
          Attendance for <?php echo date('d M Y', strtotime($selected_date)); ?>
        </strong>
        <span class="badge bg-secondary"><?php echo count($attendance_list); ?> staff</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($attendance_list)): ?>
          <div class="text-center py-5 text-muted">
            No staff found for this filter.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Staff Name</th>
                  <th>Staff ID</th>
                  <th>Department</th>
                  <th>Time In</th>
                  <th>Time Out</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($attendance_list as $row): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($row['full_name']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($row['staff_id'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($row['department'] ?: '—'); ?></td>
                    <td>
                      <?php 
                        echo $row['check_in'] 
                          ? date('h:i A', strtotime($row['check_in'])) 
                          : '—'; 
                      ?>
                    </td>
                    <td>
                      <?php 
                        echo $row['check_out'] 
                          ? date('h:i A', strtotime($row['check_out'])) 
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
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
