<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';
$staff   = null;

// Get staff ID from URL
$staff_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($staff_id <= 0) {
    header('Location: staff.php');
    exit;
}

try {
    $pdo = getDB();

    // Load staff details
    $stmt = $pdo->prepare("
        SELECT * FROM users 
        WHERE id = ? AND school_id = ? AND role = 'staff'
        LIMIT 1
    ");
    $stmt->execute([$staff_id, $school_id]);
    $staff = $stmt->fetch();

    if (!$staff) {
        header('Location: staff.php');
        exit;
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name       = trim($_POST['full_name'] ?? '');
        $staff_id_number = trim($_POST['staff_id_number'] ?? '');
        $phone           = trim($_POST['phone'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $department      = trim($_POST['department'] ?? '');
        $password        = $_POST['password'] ?? '';
        $is_active       = isset($_POST['is_active']) ? 1 : 0;

        if (empty($full_name) || empty($phone)) {
            $error = 'Full Name and Phone are required.';
        } else {
            // Check if phone already used by another staff
            $check = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND school_id = ? AND id != ?");
            $check->execute([$phone, $school_id, $staff_id]);
            if ($check->fetch()) {
                $error = 'This phone number is already used by another staff.';
            } else {
                if (!empty($password)) {
                    // Update with new password
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            full_name = ?, staff_id_number = ?, phone = ?, email = ?, 
                            department = ?, password = ?, is_active = ?
                        WHERE id = ? AND school_id = ?
                    ");
                    $stmt->execute([
                        $full_name, $staff_id_number ?: null, $phone, 
                        $email ?: null, $department ?: null, $hashed, $is_active,
                        $staff_id, $school_id
                    ]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("
                        UPDATE users SET 
                            full_name = ?, staff_id_number = ?, phone = ?, email = ?, 
                            department = ?, is_active = ?
                        WHERE id = ? AND school_id = ?
                    ");
                    $stmt->execute([
                        $full_name, $staff_id_number ?: null, $phone, 
                        $email ?: null, $department ?: null, $is_active,
                        $staff_id, $school_id
                    ]);
                }

                $success = 'Staff details updated successfully!';

                // Reload staff data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$staff_id]);
                $staff = $stmt->fetch();
            }
        }
    }

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Staff - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Edit Staff</h3>
      <a href="staff.php" class="btn btn-outline-primary btn-sm">← Back to Staff List</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required
                   value="<?php echo htmlspecialchars($staff['full_name']); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Staff ID Number</label>
            <input type="text" name="staff_id_number" class="form-control"
                   value="<?php echo htmlspecialchars($staff['staff_id_number'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control" required
                   value="<?php echo htmlspecialchars($staff['phone']); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?php echo htmlspecialchars($staff['email'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Department / Subject</label>
            <input type="text" name="department" class="form-control"
                   value="<?php echo htmlspecialchars($staff['department'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Only fill if you want to change password">
          </div>

          <div class="mb-3 form-check">
            <input type="checkbox" name="is_active" class="form-check-input" id="is_active"
                   <?php echo $staff['is_active'] ? 'checked' : ''; ?>>
            <label class="form-check-label" for="is_active">Active (can login)</label>
          </div>

          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="staff.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
