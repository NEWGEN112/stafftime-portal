<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];

$success = '';
$error   = '';
$sessions = [];
$edit_session = null;
$session_terms = [];

try {
    $pdo = getDB();

    // CREATE SESSION
    if (isset($_POST['action']) && $_POST['action'] === 'create_session') {
        $session_name = trim($_POST['session_name'] ?? '');
        $start_date   = $_POST['start_date'] ?? null;
        $end_date     = $_POST['end_date'] ?? null;

        if (empty($session_name)) {
            $error = 'Session name is required.';
        } else {
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
                $success = "Session created successfully!";
            }
        }
    }

    // UPDATE SESSION
    if (isset($_POST['action']) && $_POST['action'] === 'update_session') {
        $session_id   = intval($_POST['session_id'] ?? 0);
        $session_name = trim($_POST['session_name'] ?? '');
        $start_date   = $_POST['start_date'] ?? null;
        $end_date     = $_POST['end_date'] ?? null;

        if (empty($session_name) || $session_id <= 0) {
            $error = 'Session name is required.';
        } else {
            $stmt = $pdo->prepare("
                UPDATE sessions 
                SET session_name = ?, start_date = ?, end_date = ?
                WHERE id = ? AND school_id = ?
            ");
            $stmt->execute([$session_name, $start_date ?: null, $end_date ?: null, $session_id, $school_id]);
            $success = 'Session updated successfully!';
        }
    }

    // DELETE SESSION
    if (isset($_POST['action']) && $_POST['action'] === 'delete_session') {
        $session_id = intval($_POST['session_id'] ?? 0);
        $pdo->prepare("DELETE FROM terms WHERE session_id = ?")->execute([$session_id]);
        $pdo->prepare("DELETE FROM sessions WHERE id = ? AND school_id = ?")->execute([$session_id, $school_id]);
        $success = 'Session and its terms deleted successfully!';
    }

    // SET CURRENT SESSION
    if (isset($_POST['action']) && $_POST['action'] === 'set_current') {
        $session_id = intval($_POST['session_id'] ?? 0);
        $pdo->prepare("UPDATE sessions SET is_current = 0 WHERE school_id = ?")->execute([$school_id]);
        $pdo->prepare("UPDATE sessions SET is_current = 1 WHERE id = ? AND school_id = ?")->execute([$session_id, $school_id]);
        $success = 'Current session updated!';
    }

    // SAVE TERMS
    if (isset($_POST['action']) && $_POST['action'] === 'save_terms') {
        $session_id = intval($_POST['session_id'] ?? 0);

        $terms_data = [
            1 => [
                'name'  => trim($_POST['term1_name'] ?? 'First Term'),
                'start' => $_POST['term1_start'] ?? null,
                'end'   => $_POST['term1_end'] ?? null,
            ],
            2 => [
                'name'  => trim($_POST['term2_name'] ?? 'Second Term'),
                'start' => $_POST['term2_start'] ?? null,
                'end'   => $_POST['term2_end'] ?? null,
            ],
            3 => [
                'name'  => trim($_POST['term3_name'] ?? 'Third Term'),
                'start' => $_POST['term3_start'] ?? null,
                'end'   => $_POST['term3_end'] ?? null,
            ],
        ];

        foreach ($terms_data as $num => $term) {
            $check = $pdo->prepare("SELECT id FROM terms WHERE session_id = ? AND term_number = ?");
            $check->execute([$session_id, $num]);
            $existing = $check->fetch();

            if ($existing) {
                $stmt = $pdo->prepare("
                    UPDATE terms SET term_name = ?, start_date = ?, end_date = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $term['name'] ?: ('Term ' . $num),
                    $term['start'] ?: null,
                    $term['end'] ?: null,
                    $existing['id']
                ]);
            } else {
                $stmt = $pdo->prepare("
                    INSERT INTO terms (session_id, term_number, term_name, start_date, end_date)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $session_id,
                    $num,
                    $term['name'] ?: ('Term ' . $num),
                    $term['start'] ?: null,
                    $term['end'] ?: null
                ]);
            }
        }
        $success = 'Terms saved successfully!';
    }

    // LOAD SESSION FOR EDIT
    if (isset($_GET['edit']) && intval($_GET['edit']) > 0) {
        $edit_id = intval($_GET['edit']);
        $stmt = $pdo->prepare("SELECT * FROM sessions WHERE id = ? AND school_id = ?");
        $stmt->execute([$edit_id, $school_id]);
        $edit_session = $stmt->fetch();
    }

    // LOAD TERMS SESSION ID
    $terms_session_id = intval($_GET['terms_for'] ?? 0);
    if ($terms_session_id <= 0 && !empty($_GET['edit'])) {
        $terms_session_id = intval($_GET['edit']);
    }

    // LOAD ALL SESSIONS
    $stmt = $pdo->prepare("
        SELECT s.*, 
               (SELECT COUNT(*) FROM terms t WHERE t.session_id = s.id) as term_count
        FROM sessions s
        WHERE s.school_id = ?
        ORDER BY s.is_current DESC, s.session_name DESC
    ");
    $stmt->execute([$school_id]);
    $sessions = $stmt->fetchAll();

    if ($terms_session_id <= 0 && !empty($sessions)) {
        foreach ($sessions as $s) {
            if ($s['is_current']) {
                $terms_session_id = $s['id'];
                break;
            }
        }
        if ($terms_session_id <= 0) {
            $terms_session_id = $sessions[0]['id'];
        }
    }

    if ($terms_session_id > 0) {
        $stmt = $pdo->prepare("SELECT * FROM terms WHERE session_id = ? ORDER BY term_number");
        $stmt->execute([$terms_session_id]);
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            $session_terms[$row['term_number']] = $row;
        }
    }

} catch (Exception $e) {
    $error = 'Something went wrong. Please try again.';
}

function term_val($session_terms, $num, $field) {
    return isset($session_terms[$num][$field]) ? $session_terms[$num][$field] : '';
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

    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong><?php echo $edit_session ? 'Edit Session' : 'Create New Session'; ?></strong>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="<?php echo $edit_session ? 'update_session' : 'create_session'; ?>">
          <?php if ($edit_session): ?>
            <input type="hidden" name="session_id" value="<?php echo (int)$edit_session['id']; ?>">
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Session Name *</label>
              <input type="text" name="session_name" class="form-control" required
                     placeholder="e.g. 2025/2026"
                     value="<?php echo htmlspecialchars($edit_session['session_name'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control"
                     value="<?php echo htmlspecialchars($edit_session['start_date'] ?? ''); ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">End Date</label>
              <input type="date" name="end_date" class="form-control"
                     value="<?php echo htmlspecialchars($edit_session['end_date'] ?? ''); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">
                <?php echo $edit_session ? 'Update' : 'Create'; ?>
              </button>
            </div>
          </div>
          <?php if ($edit_session): ?>
            <div class="mt-2">
              <a href="calendar.php" class="btn btn-sm btn-outline-secondary">Cancel Edit</a>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <div class="card mb-4">
      <div class="card-header bg-white"><strong>Sessions</strong></div>
      <div class="card-body p-0">
        <?php if (empty($sessions)): ?>
          <div class="text-center py-4 text-muted">No sessions created yet.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light">
                <tr>
                  <th>Session</th>
                  <th>Start</th>
                  <th>End</th>
                  <th>Terms</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($sessions as $s): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($s['session_name']); ?></strong></td>
                    <td><?php echo $s['start_date'] ? date('d M Y', strtotime($s['start_date'])) : '—'; ?></td>
                    <td><?php echo $s['end_date'] ? date('d M Y', strtotime($s['end_date'])) : '—'; ?></td>
                    <td><?php echo (int)$s['term_count']; ?>/3</td>
                    <td>
                      <?php if ($s['is_current']): ?>
                        <span class="badge bg-success">Current</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex flex-wrap gap-1">
                        <a href="calendar.php?edit=<?php echo (int)$s['id']; ?>&terms_for=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <a href="calendar.php?terms_for=<?php echo (int)$s['id']; ?>" class="btn btn-sm btn-outline-info">Terms</a>
                        <?php if (!$s['is_current']): ?>
                          <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="set_current">
                            <input type="hidden" name="session_id" value="<?php echo (int)$s['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-success">Set Current</button>
                          </form>
                        <?php endif; ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this session and all its terms?');">
                          <input type="hidden" name="action" value="delete_session">
                          <input type="hidden" name="session_id" value="<?php echo (int)$s['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($sessions)): ?>
    <div class="card mb-4">
      <div class="card-header bg-white"><strong>Manage Terms (by Session)</strong></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="save_terms">
          <div class="mb-3">
            <label class="form-label">Select Session</label>
            <select name="session_id" class="form-select" required onchange="window.location='calendar.php?terms_for='+this.value;">
              <?php foreach ($sessions as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$s['id'] === (int)$terms_session_id) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($s['session_name']); ?>
                  <?php echo $s['is_current'] ? '(Current)' : ''; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="border rounded p-3 mb-3">
            <h6>1st Term</h6>
            <div class="row g-2">
              <div class="col-md-4">
                <input type="text" name="term1_name" class="form-control" placeholder="Term name"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 1, 'term_name') ?: 'First Term'); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term1_start" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 1, 'start_date')); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term1_end" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 1, 'end_date')); ?>">
              </div>
            </div>
          </div>

          <div class="border rounded p-3 mb-3">
            <h6>2nd Term</h6>
            <div class="row g-2">
              <div class="col-md-4">
                <input type="text" name="term2_name" class="form-control" placeholder="Term name"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 2, 'term_name') ?: 'Second Term'); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term2_start" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 2, 'start_date')); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term2_end" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 2, 'end_date')); ?>">
              </div>
            </div>
          </div>

          <div class="border rounded p-3 mb-3">
            <h6>3rd Term</h6>
            <div class="row g-2">
              <div class="col-md-4">
                <input type="text" name="term3_name" class="form-control" placeholder="Term name"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 3, 'term_name') ?: 'Third Term'); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term3_start" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 3, 'start_date')); ?>">
              </div>
              <div class="col-md-4">
                <input type="date" name="term3_end" class="form-control"
                       value="<?php echo htmlspecialchars(term_val($session_terms, 3, 'end_date')); ?>">
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Save Terms for This Session</button>
        </form>

        <hr>
        <p class="text-muted small mb-0">
          For term reports: go to <a href="reports.php">Reports</a>, choose session, then choose First/Second/Third Term.
        </p>
      </div>
    </div>
    <?php endif; ?>

  </div>
</body>
</html>
