<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();   // Must be logged in

$user = currentUser();

// Only staff should use this page (admins can also view for testing)
if ($user['role'] !== 'staff' && $user['role'] !== 'admin') {
    header('Location: ../public/login.php');
    exit;
}

$school_id = $user['school_id'];
$user_id   = $user['id'];
$today     = date('Y-m-d');
$now       = date('H:i:s');

$message = '';
$message_type = '';
$today_record = null;

// Get school late time (default 07:45 if not set)
$late_time = '07:45:00';

try {
    $pdo = getDB();

    // Get today's attendance record
    $stmt = $pdo->prepare("
        SELECT * FROM attendances 
        WHERE user_id = ? AND attendance_date = ?
        LIMIT 1
    ");
    $stmt->execute([$user_id, $today]);
    $today_record = $stmt->fetch();

    // Handle Check In
    if (isset($_POST['action']) && $_POST['action'] === 'checkin') {
        if ($today_record) {
            $message = 'You have already checked in today.';
            $message_type = 'warning';
        } else {
            // Decide status (Present or Late)
            $status = ($now > $late_time) ? 'late' : 'present';

            $stmt = $pdo->prepare("
                INSERT INTO attendances 
                (school_id, user_id, attendance_date, check_in_time, status, marked_by)
                VALUES (?, ?, ?, ?, ?, 'system')
            ");
            $stmt->execute([$school_id, $user_id, $today, $now, $status]);

            $message = 'Checked In successfully at ' . date('h:i A');
            $message_type = 'success';

            // Refresh record
            $stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? AND attendance_date = ?");
            $stmt->execute([$user_id, $today]);
            $today_record = $stmt->fetch();
        }
    }

    // Handle Check Out
    if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
        if (!$today_record) {
            $message = 'You have not checked in yet.';
            $message_type = 'warning';
        } elseif ($today_record['check_out_time']) {
            $message = 'You have already checked out today.';
            $message_type = 'warning';
        } else {
            $stmt = $pdo->prepare("
                UPDATE attendances 
                SET check_out_time = ? 
                WHERE id = ?
            ");
            $stmt->execute([$now, $today_record['id']]);

            $message = 'Checked Out successfully at ' . date('h:i A');
            $message_type = 'success';

            // Refresh record
            $stmt = $pdo->prepare("SELECT * FROM attendances WHERE user_id = ? AND attendance_date = ?");
            $stmt->execute([$user_id, $today]);
            $today_record = $stmt->fetch();
        }
    }

} catch (Exception $e) {
    $message = 'System error. Please try again later.';
    $message_type = 'danger';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check In / Out - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .check-btn {
      font-size: 1.4rem;
      padding: 22px;
      border-radius: 14px;
      font-weight: 700;
    }
    .status-card {
      border-radius: 16px;
    }
  </style>
</head>
<body>
  <div class="container py-4" style="max-width: 480px;">
    
    <div class="text-center mb-4">
      <h4 class="mb-1">Hello, <?php echo htmlspecialchars($user['full_name']); ?></h4>
      <p class="text-muted mb-0"><?php echo date('l, d M Y'); ?></p>
    </div>

    <?php if ($message): ?>
      <div class="alert alert-<?php echo $message_type; ?> text-center">
        <?php echo htmlspecialchars($message); ?>
      </div>
    <?php endif; ?>

    <!-- Today's Status -->
    <div class="card status-card mb-4">
      <div class="card-body text-center py-4">
        <p class="text-muted mb-2">Today's Status</p>

        <?php if (!$today_record): ?>
          <h3 class="text-secondary">Not Checked In</h3>
          <p class="text-muted">Tap the button below to check in</p>
        <?php else: ?>
          <?php
            $status = $today_record['status'];
            $badge_class = match($status) {
                'present' => 'success',
                'late'    => 'warning',
                'absent'  => 'danger',
                'leave'   => 'info',
                default   => 'secondary'
            };
          ?>
          <h3>
            <span class="badge bg-<?php echo $badge_class; ?> fs-5 text-uppercase">
              <?php echo $status; ?>
            </span>
          </h3>
          <p class="mb-1">
            <strong>Time In:</strong> 
            <?php echo $today_record['check_in_time'] ? date('h:i A', strtotime($today_record['check_in_time'])) : '—'; ?>
          </p>
          <p class="mb-0">
            <strong>Time Out:</strong> 
            <?php echo $today_record['check_out_time'] ? date('h:i A', strtotime($today_record['check_out_time'])) : 'Not yet'; ?>
          </p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Action Buttons -->
    <form method="POST">
      <?php if (!$today_record): ?>
        <!-- Show Check In button -->
        <button type="submit" name="action" value="checkin" class="btn btn-success check-btn w-100 mb-3">
          ✅ CHECK IN
        </button>
      <?php elseif (!$today_record['check_out_time']): ?>
        <!-- Show Check Out button -->
        <button type="submit" name="action" value="checkout" class="btn btn-danger check-btn w-100 mb-3">
          🚪 CHECK OUT
        </button>
      <?php else: ?>
        <div class="alert alert-info text-center">
          You have completed attendance for today.
        </div>
      <?php endif; ?>
    </form>

    <div class="d-grid gap-2 mt-4">
      <a href="../public/logout.php" class="btn btn-outline-secondary">Logout</a>
    </div>

  </div>
</body>
</html>
