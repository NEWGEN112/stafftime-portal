<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$user = currentUser();
$user_id = $user['id'];

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill all fields.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } else {
        try {
            $pdo = getDB();

            // Get current password hash
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $row = $stmt->fetch();

            if (!$row || !password_verify($current_password, $row['password'])) {
                $error = 'Current password is incorrect.';
            } else {
                // Update password
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashed, $user_id]);

                $success = 'Password changed successfully!';
            }
        } catch (Exception $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}

// Decide back link
$back_link = isAdmin() ? '../admin/index.php' : 'index.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Change Password - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4" style="max-width: 450px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4>Change Password</h4>
      <a href="<?php echo $back_link; ?>" class="btn btn-outline-primary btn-sm">← Back</a>
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
            <label class="form-label">Current Password *</label>
            <input type="password" name="current_password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">New Password *</label>
            <input type="password" name="new_password" class="form-control" required minlength="6">
            <div class="form-text">Minimum 6 characters</div>
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm New Password *</label>
            <input type="password" name="confirm_password" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Change Password</button>
        </form>
      </div>
    </div>

  </div>
</body>
</html>
