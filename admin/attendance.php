<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Attendance - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Attendance</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-2">
          <div class="col-md-4">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Filter by Status</label>
            <select class="form-select">
              <option>All</option>
              <option>Present</option>
              <option>Late</option>
              <option>Absent</option>
              <option>Leave</option>
            </select>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <button class="btn btn-primary w-100">Filter</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body text-center py-5">
        <h5>Daily Attendance Records</h5>
        <p class="text-muted">This page will show full attendance list for any selected date.<br>
        We will connect it to the database later.</p>
      </div>
    </div>
  </div>
</body>
</html>
