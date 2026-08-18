<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$user = currentUser();
$school_id = $user['school_id'];
$school_name = $user['school_name'] ?? 'School';

// Handle CSV download
if (isset($_GET['type'])) {
    $type = $_GET['type'];

    try {
        $pdo = getDB();

        if ($type === 'staff') {
            $stmt = $pdo->prepare("
                SELECT full_name, staff_id_number, phone, email, department, 
                       CASE WHEN is_active = 1 THEN 'Active' ELSE 'Inactive' END AS status,
                       created_at
                FROM users 
                WHERE school_id = ? AND role = 'staff'
                ORDER BY full_name ASC
            ");
            $stmt->execute([$school_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = 'staff_list_' . date('Y-m-d') . '.csv';
            $headers = ['Full Name', 'Staff ID', 'Phone', 'Email', 'Department', 'Status', 'Date Added'];

        } elseif ($type === 'attendance') {
            $from = $_GET['from'] ?? date('Y-m-01');
            $to   = $_GET['to'] ?? date('Y-m-d');

            $stmt = $pdo->prepare("
                SELECT u.full_name, u.staff_id_number, u.department,
                       a.attendance_date, a.check_in_time, a.check_out_time, a.status
                FROM attendances a
                JOIN users u ON a.user_id = u.id
                WHERE a.school_id = ?
                  AND a.attendance_date BETWEEN ? AND ?
                ORDER BY a.attendance_date DESC, u.full_name ASC
            ");
            $stmt->execute([$school_id, $from, $to]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = 'attendance_' . $from . '_to_' . $to . '.csv';
            $headers = ['Full Name', 'Staff ID', 'Department', 'Date', 'Time In', 'Time Out', 'Status'];

        } elseif ($type === 'leaves') {
            $stmt = $pdo->prepare("
                SELECT u.full_name, u.staff_id_number, u.department,
                       l.start_date, l.end_date, l.reason, l.status, l.created_at
                FROM leaves l
                JOIN users u ON l.user_id = u.id
                WHERE l.school_id = ?
                ORDER BY l.created_at DESC
            ");
            $stmt->execute([$school_id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $filename = 'leave_records_' . date('Y-m-d') . '.csv';
            $headers = ['Full Name', 'Staff ID', 'Department', 'Start Date', 'End Date', 'Reason', 'Status', 'Applied On'];

        } else {
            header('Location: export.php');
            exit;
        }

        // Output CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM for Excel to open UTF-8 correctly
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, $headers);

        foreach ($rows as $row) {
            fputcsv($output, array_values($row));
        }

        fclose($output);
        exit;

    } catch (Exception $e) {
        die('Export failed. Please try again.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Export Data - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Export / Backup Data</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <p class="text-muted mb-4">
      Download your school records as CSV files.  
      You can open them in Excel, Google Sheets, or any spreadsheet app.
    </p>

    <!-- Staff List -->
    <div class="card mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <strong>Staff List</strong>
          <br>
          <small class="text-muted">All staff names, phone, department, status</small>
        </div>
        <a href="export.php?type=staff" class="btn btn-success btn-sm">
          Download Staff CSV
        </a>
      </div>
    </div>

    <!-- Attendance -->
    <div class="card mb-3">
      <div class="card-body">
        <strong>Attendance Records</strong>
        <br>
        <small class="text-muted">Download attendance by date range</small>

        <form method="GET" class="row g-2 mt-3">
          <input type="hidden" name="type" value="attendance">
          <div class="col-6 col-md-3">
            <label class="form-label small">From</label>
            <input type="date" name="from" class="form-control form-control-sm" 
                   value="<?php echo date('Y-m-01'); ?>" required>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label small">To</label>
            <input type="date" name="to" class="form-control form-control-sm" 
                   value="<?php echo date('Y-m-d'); ?>" required>
          </div>
          <div class="col-12 col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-success btn-sm w-100">
              Download Attendance CSV
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Leaves -->
    <div class="card mb-3">
      <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <strong>Leave Records</strong>
          <br>
          <small class="text-muted">All leave applications and their status</small>
        </div>
        <a href="export.php?type=leaves" class="btn btn-success btn-sm">
          Download Leaves CSV
        </a>
      </div>
    </div>

  </div>
</body>
</html>
