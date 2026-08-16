<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$user_id   = $user['id'];
$school_id = $user['school_id'];

$success = '';
$error   = '';
$my_leaves = [];

try {
    $pdo = getDB();

    // Handle new leave application
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $start_date = $_POST['start_date'] ?? '';
        $end_date   = $_POST['end_date'] ?? '';
        $reason     = trim($_POST['reason'] ?? '');

        if (empty($start_date) || empty($end_date)) {
            $error = 'Please select start and end dates.';
        } elseif ($end_date < $start_date) {
            $error = 'End date cannot be before start date.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO leaves (school_id, user_id, start_date, end_date, reason, status)
                VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([$school_id, $user_id, $start_date, $end_date, $reason]);
            $success = 'Leave request submitted successfully! Waiting for Admin approval.';
        }
    }

    // Get my leave history
    $stmt = $pdo->prepare("
        SELECT * FROM leaves 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $my_leaves = $stmt->fetchAll();

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apply for Leave - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4" style="max-width: 500px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Apply for Leave</h4>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Leave Form -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Start Date *</label>
            <input type="date" name="start_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">End Date *</label>
            <input type="date" name="end_date" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Reason</label>
            <textarea name="reason" class="form-control" rows="3" placeholder="Why do you need leave?"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100">Submit Leave Request</button>
        </form>
      </div>
    </div>

    <!-- My Leave History -->
    <div class="card">
      <div class="card-header bg-white">
        <strong>My Leave History</strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($my_leaves)): ?>
          <div class="text-center py-4 text-muted">No leave requests yet.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($my_leaves as $leave): ?>
              <div class="list-group-item">
                <div class="d-flex justify-content-between">
                  <div>
                    <strong>
                      <?php echo date('d M', strtotime($leave['start_date'])); ?> 
                      – 
                      <?php echo date('d M Y', strtotime($leave['end_date'])); ?>
                    </strong>
                    <br>
                    <small class="text-muted"><?php echo htmlspecialchars($leave['reason'] ?: 'No reason'); ?></small>
                  </div>
                  <div>
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
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</body>
</html>
