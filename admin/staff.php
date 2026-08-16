<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$staff_list = [];
$error = '';

try {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT id, full_name, staff_id_number, phone, email, department, role, is_active, created_at
        FROM users 
        WHERE school_id = ? AND role = 'staff'
        ORDER BY full_name ASC
    ");
    $stmt->execute([$school_id]);
    $staff_list = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Could not load staff list. Please check database connection.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Staff - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h3 class="mb-0">Manage Staff</h3>
        <small class="text-muted">Total: <?php echo count($staff_list); ?> staff</small>
      </div>
      <div>
        <a href="staff-add.php" class="btn btn-primary btn-sm">+ Add New Staff</a>
        <a href="index.php" class="btn btn-outline-primary btn-sm">← Dashboard</a>
      </div>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
      <div class="card-body p-0">
        <?php if (empty($staff_list)): ?>
          <div class="text-center py-5">
            <h5>No staff added yet</h5>
            <p class="text-muted">Click the button below to add your first staff member.</p>
            <a href="staff-add.php" class="btn btn-primary">+ Add New Staff</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Name</th>
                  <th>Staff ID</th>
                  <th>Phone</th>
                  <th>Department</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($staff_list as $staff): ?>
                  <tr>
                    <td>
                      <strong><?php echo htmlspecialchars($staff['full_name']); ?></strong>
                      <?php if ($staff['email']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($staff['email']); ?></small>
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($staff['staff_id_number'] ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($staff['phone']); ?></td>
                    <td><?php echo htmlspecialchars($staff['department'] ?: '—'); ?></td>
                    <td>
                      <?php if ($staff['is_active']): ?>
                        <span class="badge bg-success">Active</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">Inactive</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="#" class="btn btn-sm btn-outline-primary">Edit</a>
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
