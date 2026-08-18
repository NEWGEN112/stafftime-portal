<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';
$school  = null;

try {
    $pdo = getDB();

    // Load current school data
    $stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
    $stmt->execute([$school_id]);
    $school = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        // Update School Information
        if ($action === 'update_school') {
            $school_name = trim($_POST['school_name'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $address     = trim($_POST['address'] ?? '');
            $state       = trim($_POST['state'] ?? '');

            if (empty($school_name)) {
                $error = 'School name is required.';
            } else {
                $stmt = $pdo->prepare("
                    UPDATE schools 
                    SET school_name = ?, phone = ?, email = ?, address = ?, state = ?
                    WHERE id = ?
                ");
                $stmt->execute([$school_name, $phone, $email, $address, $state, $school_id]);

                $_SESSION['school_name'] = $school_name;
                $success = 'School information updated successfully!';

                $stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
                $stmt->execute([$school_id]);
                $school = $stmt->fetch();
            }
        }

        // Update Attendance Times (Late Time + Closing Time)
        if ($action === 'update_times') {
            $late_time    = $_POST['late_time'] ?? '07:45';
            $closing_time = $_POST['closing_time'] ?? '14:00';

            // Make sure format is HH:MM:SS
            if (strlen($late_time) === 5) {
                $late_time .= ':00';
            }
            if (strlen($closing_time) === 5) {
                $closing_time .= ':00';
            }

            try {
                $stmt = $pdo->prepare("
                    UPDATE schools 
                    SET late_time = ?, closing_time = ?
                    WHERE id = ?
                ");
                $stmt->execute([$late_time, $closing_time, $school_id]);
                $success = 'Attendance times updated successfully!';

                $stmt = $pdo->prepare("SELECT * FROM schools WHERE id = ?");
                $stmt->execute([$school_id]);
                $school = $stmt->fetch();
            } catch (Exception $e) {
                $error = 'Could not save times. Please make sure closing_time column exists.';
            }
        }
    }

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}

// Current times for the form
$current_late = '07:45';
$current_closing = '14:00';

if (!empty($school['late_time'])) {
    $current_late = substr($school['late_time'], 0, 5);
}
if (!empty($school['closing_time'])) {
    $current_closing = substr($school['closing_time'], 0, 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>School Settings</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- School Information -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>School Information</strong>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="update_school">

          <div class="mb-3">
            <label class="form-label">School Name *</label>
            <input type="text" name="school_name" class="form-control" required
                   value="<?php echo htmlspecialchars($school['school_name'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control"
                   value="<?php echo htmlspecialchars($school['phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($school['email'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control"
                   value="<?php echo htmlspecialchars($school['address'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control"
                   value="<?php echo htmlspecialchars($school['state'] ?? ''); ?>">
          </div>

          <button type="submit" class="btn btn-primary">Save School Information</button>
        </form>
      </div>
    </div>

    <!-- Attendance Times -->
    <div class="card">
      <div class="card-header bg-white">
        <strong>Attendance Times</strong>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="update_times">

          <div class="mb-3">
            <label class="form-label">Late Time (Morning)</label>
            <input type="time" name="late_time" class="form-control" 
                   value="<?php echo htmlspecialchars($current_late); ?>">
            <div class="form-text">
              Any staff who checks in after this time will be marked as <strong>Late</strong>.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">School Closing Time</label>
            <input type="time" name="closing_time" class="form-control" 
                   value="<?php echo htmlspecialchars($current_closing); ?>">
            <div class="form-text">
              Used for automatic Check-Out and Mark Absent after school closes.
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Attendance Times</button>
        </form>
      </div>
    </div>

  </div>
</body>
</html>
