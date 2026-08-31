<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, send each person to their correct portal
if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: ../owner/index.php');
    } elseif (isAdmin()) {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../staff/index.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    $csrf = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrf)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email_or_phone = trim($_POST['email_or_phone'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email_or_phone) || empty($password)) {
            $error = 'Please enter email/phone and password.';
        } else {
            try {
                $pdo = getDB();

                $stmt = $pdo->prepare("
                    SELECT u.*, s.school_name, s.school_code 
                    FROM users u 
                    JOIN schools s ON u.school_id = s.id 
                    WHERE (u.email = ? OR u.phone = ?) 
                    AND u.is_active = 1
                    LIMIT 1
                ");
                $stmt->execute([$email_or_phone, $email_or_phone]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password'])) {
                    // Stronger session
                    secureLoginSession();

                    $_SESSION['user_id']     = $user['id'];
                    $_SESSION['school_id']   = $user['school_id'];
                    $_SESSION['full_name']   = $user['full_name'];
                    $_SESSION['role']        = $user['role'];
                    $_SESSION['school_name'] = $user['school_name'];
                    $_SESSION['school_code'] = $user['school_code'];

                    if ($user['role'] === 'super_admin') {
                        header('Location: ../owner/index.php');
                    } elseif ($user['role'] === 'admin') {
                        header('Location: ../admin/index.php');
                    } else {
                        header('Location: ../staff/index.php');
                    }
                    exit;
                } else {
                    $error = 'Invalid email/phone or password.';
                }
            } catch (Exception $e) {
                $error = 'Login failed. Please try again later.';
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
  <title>Login - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; }
    .login-card {
      max-width: 420px;
      margin: 60px auto;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="card login-card">
      <div class="card-body p-4">
        <div class="text-center mb-4">
          <h3 class="text-primary">StaffTime</h3>
          <p class="text-muted">Login to your account</p>
        </div>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

          <div class="mb-3">
            <label class="form-label">Email or Phone Number</label>
            <input type="text" name="email_or_phone" class="form-control" required 
                   placeholder="Enter email or phone" 
                   value="<?php echo htmlspecialchars($_POST['email_or_phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required placeholder="Enter password">
          </div>

          <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
        </form>

        <div class="text-center mt-4">
          <p class="mb-1">Don't have a school account?</p>
          <a href="register.php" class="btn btn-outline-primary btn-sm">Register Your School</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
