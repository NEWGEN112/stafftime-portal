<?php
session_start();
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

    <!-- Create Session -->
    <div class="card mb-4">
      <div class="card-header bg-white">
        <strong>Create New Session</strong>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Session Name</label>
            <input type="text" class="form-control" placeholder="e.g. 2025/2026">
          </div>
          <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" class="form-control">
          </div>
          <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Create</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Set Terms -->
    <div class="card">
      <div class="card-header bg-white">
        <strong>Set Terms for Current Session</strong>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">First Term</label>
          <div class="row g-2">
            <div class="col"><input type="date" class="form-control" placeholder="Start"></div>
            <div class="col"><input type="date" class="form-control" placeholder="End"></div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Second Term</label>
          <div class="row g-2">
            <div class="col"><input type="date" class="form-control"></div>
            <div class="col"><input type="date" class="form-control"></div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Third Term</label>
          <div class="row g-2">
            <div class="col"><input type="date" class="form-control"></div>
            <div class="col"><input type="date" class="form-control"></div>
          </div>
        </div>
        <button class="btn btn-primary">Save Terms</button>
      </div>
    </div>
  </div>
</body>
</html>
