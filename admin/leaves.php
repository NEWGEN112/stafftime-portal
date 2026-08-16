<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Leave Requests - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Leave Requests</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Pending Leave Requests</h5>
        
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr>
                <th>Staff Name</th>
                <th>From</th>
                <th>To</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Sade Adebayo</td>
                <td>2026-08-18</td>
                <td>2026-08-20</td>
                <td>Family emergency</td>
                <td><span class="badge bg-warning text-dark">Pending</span></td>
                <td>
                  <button class="btn btn-sm btn-success">Approve</button>
                  <button class="btn btn-sm btn-danger">Reject</button>
                </td>
              </tr>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  More leave requests will appear here when staff apply.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
