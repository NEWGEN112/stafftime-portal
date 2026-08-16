<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Attendance Reports</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="mb-4">Generate Report</h5>

        <div class="mb-3">
          <label class="form-label">Select Session</label>
          <select class="form-select">
            <option>2025/2026</option>
            <option>2024/2025</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Select Term</label>
          <select class="form-select">
            <option>First Term</option>
            <option>Second Term</option>
            <option>Third Term</option>
            <option>Full Session (All 3 Terms)</option>
          </select>
        </div>

        <button class="btn btn-primary">Generate Report</button>
        <button class="btn btn-outline-success ms-2">Download PDF</button>
      </div>
    </div>

    <div class="card mt-4">
      <div class="card-body text-center py-4">
        <p class="text-muted mb-0">PDF reports will appear here after generation.<br>
        (We will build the real PDF later)</p>
      </div>
    </div>
  </div>
</body>
</html>
