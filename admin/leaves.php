<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];
$admin_id  = $user['id'];

$success = '';
$error   = '';
$leaves  = [];

try {
    $pdo = getDB();

    // Handle Approve / Reject
    if (isset($_POST['action']) && isset($_POST['leave_id'])) {
        $leave_id = intval($_POST['leave_id']);
        $action   = $_POST['action'];

        if ($action === 'approve' || $action === 'reject') {
            $new_status = ($action === 'approve') ? 'approved' : 'rejected';

            $stmt = $pdo->prepare("
                UPDATE leaves 
                SET status = ?, approved_by = ?, updated_at = NOW()
                WHERE id = ? AND school_id = ?
            ");
            $stmt->execute([$new_status, $admin_id, $leave_id, $school_id]);

            // If approved, mark the days as 'leave' in attendance
            if ($action === 'approve') {
                $leaveStmt = $pdo->prepare("SELECT user_id, start_date, end_date FROM leaves WHERE id = ?");
                $leaveStmt->execute([$leave_id]);
                $leave = $leaveStmt->fetch();

                if ($leave) {
                    $start = new DateTime($leave['start_date']);
                    $end   = new DateTime($leave['end_date']);
                    $end->modify('+1 day');

                    $interval = new DateInterval('P1D');
                    $period   = new DatePeriod($start, $interval, $end);

                    foreach ($period as $date) {
                        $day = $date->format('Y-m-d');

                        // Check if record already exists
                        $check = $pdo->prepare("SELECT id FROM attendances WHERE user_id = ? AND attendance_date = ?");
                        $check->execute([$leave['user_id'], $day]);
                        $existing = $check->fetch();

                        if ($existing) {
                            $pdo->prepare("UPDATE attendances SET status = 'leave' WHERE id = ?")
                                ->execute([$existing['id']]);
                        } else {
                            $pdo->prepare("
                                INSERT INTO attendances (school_id, user_id, attendance_date, status, marked_by)
                                VALUES (?, ?, ?, 'leave', 'admin')
                            ")->execute([$school_id, $leave['user_id'], $day]);
                        }
                    }
                }
            }

            $success = 'Leave request has been ' . $new_status . '.';
        }
    }

    // Load all leave requests for this school
    $stmt = $pdo->prepare("
        SELECT l.*, u.full_name, u.department
        FROM leaves l
        JOIN users u ON l.user_id = u.id
        WHERE l.school_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$school_id]);
    $leaves = $stmt->fetchAll();

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Requests - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Leave Requests</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body p-0">
        <?php if (empty($leaves)): ?>
          <div class="text-center py-5 text-muted">
            No leave requests yet.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Staff Name</th>
                  <th>Department</th>
                  <th>From</th>
                  <th>To</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($leaves as $leave): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($leave['full_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($leave['department'] ?: '—'); ?></td>
                    <td><?php echo date('d M Y', strtotime($leave['start_date'])); ?></td>
                    <td><?php echo date('d M Y', strtotime($leave['end_date'])); ?></td>
                    <td><?php echo htmlspecialchars($leave['reason'] ?: '—'); ?></td>
                    <td>
                      <?php
                        $badge = match($leave['status']) {
                          'pending'  => 'warning',
                          'approved' => 'success',
                          'rejected' => 'danger',
                          default    => 'secondary'
                        };
                      ?>
                      <span class="badge bg-<?php echo $badge; ?>">
                        <?php echo ucfirst($leave['status']); ?>
                      </span>
                    </td>
                    <td>
                      <?php if ($leave['status'] === 'pending'): ?>
                        <form method="POST" style="display:inline;">
                          <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                          <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                          <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                        </form>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
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
