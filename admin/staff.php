<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';
$staff_list = [];

try {
    $pdo = getDB();

    // Handle Deactivate / Activate / Delete
    if (isset($_POST['action']) && isset($_POST['staff_id'])) {
        $staff_id = intval($_POST['staff_id']);
        $action   = $_POST['action'];

        // Make sure the staff belongs to this school
        $check = $pdo->prepare("SELECT id, full_name FROM users WHERE id = ? AND school_id = ? AND role = 'staff'");
        $check->execute([$staff_id, $school_id]);
        $staff = $check->fetch();

        if (!$staff) {
            $error = 'Staff not found.';
        } else {
            if ($action === 'deactivate') {
                $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$staff_id]);
                $success = htmlspecialchars($staff['full_name']) . ' has been deactivated.';
            } 
            elseif ($action === 'activate') {
                $pdo->prepare("UPDATE users SET is_active = 1 WHERE id = ?")->execute([$staff_id]);
                $success = htmlspecialchars($staff['full_name']) . ' has been activated.';
            } 
            elseif ($action === 'delete') {
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$staff_id]);
                $success = htmlspecialchars($staff['full_name']) . ' has been permanently deleted.';
            }
        }
    }

    // Load staff list
    $stmt = $pdo->prepare("
        SELECT id, full_name, staff_id_number, phone, email, department, is_active, created_at
        FROM users 
        WHERE school_id = ? AND role = 'staff'
        ORDER BY is_active DESC, full_name ASC
    ");
    $stmt->execute([$school_id]);
    $staff_list = $stmt->fetchAll();

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
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

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
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
                  <tr class="<?php echo $staff['is_active'] ? '' : 'table-secondary'; ?>">
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
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to do this?');">
                        <input type="hidden" name="staff_id" value="<?php echo $staff['id']; ?>">

                        <?php if ($staff['is_active']): ?>
                          <button type="submit" name="action" value="deactivate" class="btn btn-sm btn-warning">Deactivate</button>
                        <?php else: ?>
                          <button type="submit" name="action" value="activate" class="btn btn-sm btn-success">Activate</button>
                        <?php endif; ?>

                        <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Delete</button>
                      </form>
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
