<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';
$sessions = [];

try {
    $pdo = getDB();

    // ========== CREATE NEW SESSION ==========
    if (isset($_POST['action']) && $_POST['action'] === 'create_session') {
        $session_name = trim($_POST['session_name'] ?? '');
        $start_date   = $_POST['start_date'] ?? null;
        $end_date     = $_POST['end_date'] ?? null;

        if (empty($session_name)) {
            $error = 'Session name is required.';
        } else {
            // Check if session already exists
            $check = $pdo->prepare("SELECT id FROM sessions WHERE school_id = ? AND session_name = ?");
            $check->execute([$school_id, $session_name]);
            if ($check->fetch()) {
                $error = 'This session already exists.';
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO sessions (school_id, session_name, start_date, end_date, is_current)
                    VALUES (?, ?, ?, ?, 0)
                ");
                $stmt->execute([$school_id, $session_name, $start_date ?: null, $end_date ?: null]);
                $success = "Session <strong>$session_name</strong> created successfully!";
            }
        }
    }

    // ========== SET TERMS ==========
    if (isset($_POST['action']) && $_POST['action'] === 'save_terms') {
        $session_id = intval($_POST['session_id'] ?? 0);

        $terms = [
            1 => ['name' => 'First Term',  'start' => $_POST['term1_start'] ?? null, 'end' => $_POST['term1_end'] ?? null],
            2 => ['name' => 'Second Term', 'start' => $_POST['term2_start'] ?? null, 'end' => $_POST['term2_end'] ?? null],
            3 => ['name' => 'Third Term',  'start' => $_POST['term3_start'] ?? null, 'end' => $_POST['term3_end'] ?? null],
        ];

        foreach ($terms as $num => $term) {
            // Check if term already exists
            $check = $pdo->prepare("SELECT id FROM terms WHERE session_id = ? AND term_number = ?");
            $check->execute([$session_id, $num]);
            $existing = $check->fetch();

            if ($existing) {
                // Update
                $stmt = $pdo->prepare("
                    UPDATE terms SET term_name = ?, start_date = ?, end_date = ?
                    WHERE id = ?
                ");
                $stmt->execute([$term['name'], $term['start'] ?: null, $term['end'] ?: null, $existing['id']]);
            } else {
                // Insert
                $stmt = $pdo->prepare("
                    INSERT INTO terms (session_id, term_number, term_name, start_date, end_date)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$session_id, $num, $term['name'], $term['start'] ?: null, $term['end'] ?: null]);
            }
        }
        $success = 'Terms saved successfully!';
    }

    // ========== SET AS CURRENT SESSION ==========
    if (isset($_POST['action']) && $_POST['action'] === 'set_current') {
        $session_id = intval($_POST['session_id'] ?? 0);

        // First remove current from all
        $pdo->prepare("UPDATE sessions SET is_current = 0 WHERE school_id = ?")->execute([$school_id]);
        // Set new current
        $pdo->prepare("UPDATE sessions SET is_current = 1 WHERE id = ? AND school_id = ?")->execute([$session_id, $school_id]);
        $success = 'Current session updated!';
    }

    // ========== LOAD ALL SESSIONS ==========
    $stmt = $pdo->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM terms t WHERE t.session_id = s.id) as term_count
        FROM sessions s
        WHERE s.school_id = ?
        ORDER BY s.session_name DESC
    ");
    $stmt->execute([$school_id]);
    $sessions = $stmt->fetchAll();

} catch (Exception $e) {
    $error = 'Database error. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Academic Calendar - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Academic Calendar</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Create New Session -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>Create New Session</strong>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="create_session">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Session Name *</label>
              <input type="text" name="session_name" class="form-control" required placeholder="e.g. 2025/2026">
            </div>
            <div class="col-md-3">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Create</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Existing Sessions -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>Existing Sessions</strong>
      </div>
      <div class="card-body p-0">
        <?php if (empty($sessions)): ?>
          <div class="text-center py-4 text-muted">No sessions created yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th>Session</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Terms</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sessions as $s): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($s['session_name']); ?></strong></td>
                    <td><?php echo $s['start_date'] ? date('d M Y', strtotime($s['start_date'])) : '—'; ?></td>
                    <td><?php echo $s['end_date'] ? date('d M Y', strtotime($s['end_date'])) : '—'; ?></td>
                    <td><?php echo $s['term_count']; ?>/3</td>
                    <td>
                      <?php if ($s['is_current']): ?>
                        <span class="badge bg-success">Current</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!$s['is_current']): ?>
                        <form method="POST" style="display:inline;">
                          <input type="hidden" name="action" value="set_current">
                          <input type="hidden" name="session_id" value="<?php echo $s['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-success">Set as Current</button>
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

    <!-- Set Terms -->
    <?php if (!empty($sessions)): ?>
    <div class="card">
      <div class="card-header bg-white">
        <strong>Set Terms for a Session</strong>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="save_terms">

          <div class="mb-3">
            <label class="form-label">Select Session</label>
            <select name="session_id" class="form-select" required>
              <?php foreach ($sessions as $s): ?>
                <option value="<?php echo $s['id']; ?>">
                  <?php echo htmlspecialchars($s['session_name']); ?>
                  <?php echo $s['is_current'] ? '(Current)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">First Term</label>
            <div class="row g-2">
              <div class="col"><input type="date" name="term1_start" class="form-control" placeholder="Start"></div>
              <div class="col"><input type="date" name="term1_end" class="form-control" placeholder="End"></div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Second Term</label>
            <div class="row g-2">
              <div class="col"><input type="date" name="term2_start" class="form-control"></div>
              <div class="col"><input type="date" name="term2_end" class="form-control"></div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Third Term</label>
            <div class="row g-2">
              <div class="col"><input type="date" name="term3_start" class="form-control"></div>
              <div class="col"><input type="date" name="term3_end" class="form-control"></div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Terms</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

  </div>
</body>
</html>
