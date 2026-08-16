<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Settings - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>School Settings</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>School Information</strong>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">School Name</label>
          <input type="text" class="form-control" value="Glory Secondary School">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" value="08012345678">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" value="admin@gloryschool.com">
        </div>
        <button class="btn btn-primary">Save Changes</button>
      </div>
    </div>

    <div class="card">
      <div class="card-header bg-white">
        <strong>Attendance Settings</strong>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Late Time (anyone who checks in after this time is marked Late)</label>
          <input type="time" class="form-control" value="07:45">
        </div>
        <button class="btn btn-primary">Save Settings</button>
      </div>
    </div>
  </div>
</body>
</html>
