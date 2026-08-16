<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name       = trim($_POST['full_name'] ?? '');
    $staff_id_number = trim($_POST['staff_id_number'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $department      = trim($_POST['department'] ?? '');
    $role            = $_POST['role'] ?? 'Teacher';
    $password        = $_POST['password'] ?? '';

    // Basic validation
    if (empty($full_name) || empty($phone) || empty($password)) {
        $error = 'Full Name, Phone Number and Password are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $pdo = getDB();

            // Check if phone already exists in this school
            $check = $pdo->prepare("SELECT id FROM users WHERE phone = ? AND school_id = ?");
            $check->execute([$phone, $school_id]);
            if ($check->fetch()) {
                $error = 'A staff member with this phone number already exists in your school.';
            } else {
                // Check email if provided
                if (!empty($email)) {
                    $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ? AND school_id = ?");
                    $checkEmail->execute([$email, $school_id]);
                    if ($checkEmail->fetch()) {
                        $error = 'This email is already used by another staff in your school.';
                    }
                }

                if (empty($error)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("
                        INSERT INTO users 
                        (school_id, full_name, email, phone, password, role, staff_id_number, department, is_active)
                        VALUES (?, ?, ?, ?, ?, 'staff', ?, ?, 1)
                    ");

                    $stmt->execute([
                        $school_id,
                        $full_name,
                        $email ?: null,
                        $phone,
                        $hashed,
                        $staff_id_number ?: null,
                        $department ?: null
                    ]);

                    $success = "Staff <strong>" . htmlspecialchars($full_name) . "</strong> added successfully!";
                    
                    // Clear form values after success
                    $_POST = [];
                }
            }
        } catch (Exception $e) {
            $error = 'Failed to add staff. Please check your database connection.';
            // For debugging you can temporarily show: $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Staff - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Add New Staff</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success">
        <?php echo $success; ?>
        <div class="mt-2">
          <a href="staff-add.php" class="btn btn-sm btn-success">Add Another Staff</a>
          <a href="staff.php" class="btn btn-sm btn-outline-primary">View All Staff</a>
        </div>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required 
                   placeholder="e.g. Adebayo Johnson"
                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Staff ID Number</label>
            <input type="text" name="staff_id_number" class="form-control" 
                   placeholder="e.g. EMP001"
                   value="<?php echo htmlspecialchars($_POST['staff_id_number'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control" required 
                   placeholder="08012345678"
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" 
                   placeholder="staff@email.com"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Department / Subject</label>
            <input type="text" name="department" class="form-control" 
                   placeholder="e.g. Mathematics"
                   value="<?php echo htmlspecialchars($_POST['department'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="Teacher" <?php echo (($_POST['role'] ?? '') === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
              <option value="Admin Staff" <?php echo (($_POST['role'] ?? '') === 'Admin Staff') ? 'selected' : ''; ?>>Admin Staff</option>
              <option value="Security" <?php echo (($_POST['role'] ?? '') === 'Security') ? 'selected' : ''; ?>>Security</option>
              <option value="Other" <?php echo (($_POST['role'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Password for Staff *</label>
            <input type="password" name="password" class="form-control" required 
                   placeholder="Password they will use to login">
            <div class="form-text">Minimum 6 characters</div>
          </div>

          <button type="submit" class="btn btn-primary">Add Staff</button>
          <a href="staff.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
