<?php
require_once __DIR__ . '/../includes/auth.php';
requireSuperAdmin();

$user = currentUser();
$success = '';
$error = '';
$codes = [];
$schools = [];

try {
    $pdo = getDB();

    // ========== GENERATE NEW CODE ==========
    if (isset($_POST['action']) && $_POST['action'] === 'generate_code') {
        $notes = trim($_POST['notes'] ?? '');
        // Example: ST-2026-A1B2
        $code = 'ST-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

        $stmt = $pdo->prepare("
            INSERT INTO registration_codes (code, is_used, notes)
            VALUES (?, 0, ?)
        ");
        $stmt->execute([$code, $notes ?: null]);
        $success = "New registration code created: <strong>" . htmlspecialchars($code) . "</strong>";
    }

    // ========== DELETE UNUSED CODE ==========
    if (isset($_POST['action']) && $_POST['action'] === 'delete_code') {
        $code_id = intval($_POST['code_id'] ?? 0);
        $pdo->prepare("DELETE FROM registration_codes WHERE id = ? AND is_used = 0")->execute([$code_id]);
        $success = 'Unused code deleted.';
    }

    // Load codes
    $stmt = $pdo->query("
        SELECT rc.*, s.school_name
        FROM registration_codes rc
        LEFT JOIN schools s ON rc.used_by_school_id = s.id
        ORDER BY rc.created_at DESC
    ");
    $codes = $stmt->fetchAll();

    // Load all real schools (exclude platform school)
    $stmt = $pdo->query("
        SELECT s.*,
               (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.role = 'staff') AS staff_count,
               (SELECT COUNT(*) FROM users u WHERE u.school_id = s.id AND u.role = 'admin') AS admin_count
        FROM schools s
        WHERE s.school_code != 'ST-PLATFORM'
        ORDER BY s.id DESC
    ");
    $schools = $stmt->fetchAll();

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Owner Dashboard - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f4f6f9; }
    .card { border: none; border-radius: 14px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">StaffTime Owner</span>
      <a href="../public/logout.php" class="btn btn-sm btn-outline-light">Logout</a>
    </div>
  </nav>

  <div class="container py-4">
    <h4 class="mb-1">Platform Owner Dashboard</h4>
    <p class="text-muted mb-4">Generate registration codes and view all schools.</p>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Generate Code -->
    <div class="card mb-4">
      <div class="card-header bg-white"><strong>Generate Registration Code</strong></div>
      <div class="card-body">
        <form method="POST" class="row g-2">
          <input type="hidden" name="action" value="generate_code">
          <div class="col-md-8">
            <input type="text" name="notes" class="form-control"
                   placeholder="Note (optional) e.g. Glory Secondary School - paid">
          </div>
          <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Generate Code</button>
          </div>
        </form>
        <div class="form-text mt-2">
          Send the code to the school. They must enter it when registering.
        </div>
      </div>
    </div>

    <!-- Codes List -->
    <div class="card mb-4">
      <div class="card-header bg-white"><strong>Registration Codes</strong></div>
      <div class="card-body p-0">
        <?php if (empty($codes)): ?>
          <div class="text-center py-4 text-muted">No codes yet. Generate one above.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Code</th>
                  <th>Status</th>
                  <th>Used By</th>
                  <th>Note</th>
                  <th>Created</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($codes as $c): ?>
                  <tr>
                    <td><code class="fw-bold"><?php echo htmlspecialchars($c['code']); ?></code></td>
                    <td>
                      <?php if ($c['is_used']): ?>
                        <span class="badge bg-secondary">Used</span>
                      <?php else: ?>
                        <span class="badge bg-success">Available</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo $c['school_name'] ? htmlspecialchars($c['school_name']) : '—'; ?></td>
                    <td><?php echo htmlspecialchars($c['notes'] ?? '—'); ?></td>
                    <td class="small text-muted">
                      <?php echo date('d M Y H:i', strtotime($c['created_at'])); ?>
                    </td>
                    <td>
                      <?php if (!$c['is_used']): ?>
                        <form method="POST" onsubmit="return confirm('Delete this unused code?');">
                          <input type="hidden" name="action" value="delete_code">
                          <input type="hidden" name="code_id" value="<?php echo (int)$c['id']; ?>">
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
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

    <!-- All Schools -->
    <div class="card mb-4">
      <div class="card-header bg-white"><strong>Registered Schools</strong></div>
      <div class="card-body p-0">
        <?php if (empty($schools)): ?>
          <div class="text-center py-4 text-muted">No schools registered yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>School</th>
                  <th>Code</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Admins</th>
                  <th>Staff</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($schools as $s): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($s['school_name']); ?></strong></td>
                    <td><code><?php echo htmlspecialchars($s['school_code']); ?></code></td>
                    <td><?php echo htmlspecialchars($s['phone'] ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($s['email'] ?? '—'); ?></td>
                    <td><?php echo (int)$s['admin_count']; ?></td>
                    <td><?php echo (int)$s['staff_count']; ?></td>
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
