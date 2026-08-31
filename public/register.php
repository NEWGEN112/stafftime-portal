<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, redirect correctly
if (isLoggedIn()) {
    if (function_exists('isSuperAdmin') && isSuperAdmin()) {
        header('Location: ../owner/index.php');
    } elseif (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../staff/index.php');
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $reg_code    = strtoupper(trim($_POST['registration_code'] ?? ''));
        $school_name = trim($_POST['school_name'] ?? '');
        $address     = trim($_POST['address'] ?? '');
        $state       = trim($_POST['state'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirm     = $_POST['confirm_password'] ?? '';

        if (empty($reg_code) || empty($school_name) || empty($phone) || empty($email) || empty($password)) {
            $error = 'Please fill all required fields, including registration code.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            try {
                $pdo = getDB();

                // Validate registration code
                $codeStmt = $pdo->prepare("
                    SELECT * FROM registration_codes
                    WHERE code = ? AND is_used = 0
                    LIMIT 1
                ");
                $codeStmt->execute([$reg_code]);
                $codeRow = $codeStmt->fetch();

                if (!$codeRow) {
                    $error = 'Invalid or already used registration code. Contact StaffTime owner.';
                } else {
                    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                    $check->execute([$email]);
                    if ($check->fetch()) {
                        $error = 'This email is already registered.';
                    } else {
                        $school_code = 'ST-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));

                        $pdo->beginTransaction();

                        $stmt = $pdo->prepare("
                            INSERT INTO schools (school_name, school_code, address, state, phone, email)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([$school_name, $school_code, $address, $state, $phone, $email]);
                        $school_id = $pdo->lastInsertId();

                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("
                            INSERT INTO users (school_id, full_name, email, phone, password, role, is_active)
                            VALUES (?, ?, ?, ?, ?, 'admin', 1)
                        ");
                        $stmt->execute([$school_id, $school_name . ' Admin', $email, $phone, $hashed]);

                        // Mark registration code as used
                        $pdo->prepare("
                            UPDATE registration_codes
                            SET is_used = 1, used_by_school_id = ?, used_at = NOW()
                            WHERE id = ?
                        ")->execute([$school_id, $codeRow['id']]);

                        $pdo->commit();

                        $success = "School registered successfully!<br>
                                    School Code: <strong>" . htmlspecialchars($school_code) . "</strong><br>
                                    You can now <a href='login.php'>Login</a>.";
                    }
                }
            } catch (Exception $e) {
                if (isset($pdo) && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$csrf_token = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register School - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .register-card {
      max-width: 500px;
      margin: 40px auto;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card register-card">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <h3 class="text-primary">StaffTime</h3>
          <p class="text-muted">Register your school</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo $success; ?></div>
        <?php else: ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

          <div class="mb-3">
            <label class="form-label">Registration Code *</label>
            <input type="text" name="registration_code" class="form-control" required
                   placeholder="e.g. ST-2026-A1B2"
                   value="<?php echo htmlspecialchars($_POST['registration_code'] ?? ''); ?>">
            <div class="form-text">Get this code from the StaffTime owner after payment.</div>
          </div>

          <div class="mb-3">
            <label class="form-label">School Name *</label>
            <input type="text" name="school_name" class="form-control" required
                   placeholder="e.g. Glory Secondary School"
                   value="<?php echo htmlspecialchars($_POST['school_name'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" placeholder="School address"
                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" placeholder="e.g. Lagos"
                   value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control" required placeholder="08012345678"
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required placeholder="admin@school.com"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password *</label>
            <input type="password" name="password" class="form-control" required placeholder="Create a password">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm Password *</label>
            <input type="password" name="confirm_password" class="form-control" required placeholder="Confirm password">
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">Create School Account</button>
        </form>

        <?php endif; ?>

        <div class="text-center mt-4">
          <p class="mb-0">Already have an account? <a href="login.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
