<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();   // Protect this page
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
      <h3>Manage Staff</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card">
      <div class="card-body text-center py-5">
        <h5>Staff List Page</h5>
        <p class="text-muted">This page will show all staff members.<br>We will build it properly later.</p>
        <a href="staff-add.php" class="btn btn-primary mt-3">+ Add New Staff</a>
      </div>
    </div>
  </div>
</body>
</html>
