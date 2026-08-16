<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Add New Staff - StaffTime</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Add New Staff</h3>
      <a href="index.php" class="btn btn-outline-primary btn-sm">← Back to Dashboard</a>
    </div>

    <div class="card">
      <div class="card-body">
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Full Name *</label>
            <input type="text" name="full_name" class="form-control" required placeholder="e.g. Adebayo Johnson">
          </div>

          <div class="mb-3">
            <label class="form-label">Staff ID Number</label>
            <input type="text" name="staff_id_number" class="form-control" placeholder="e.g. EMP001">
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number *</label>
            <input type="tel" name="phone" class="form-control" required placeholder="08012345678">
          </div>

          <div class="mb-3">
            <label class="form-label">Email (optional)</label>
            <input type="email" name="email" class="form-control" placeholder="staff@email.com">
          </div>

          <div class="mb-3">
            <label class="form-label">Department / Subject</label>
            <input type="text" name="department" class="form-control" placeholder="e.g. Mathematics">
          </div>

          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
              <option value="Teacher">Teacher</option>
              <option value="Admin Staff">Admin Staff</option>
              <option value="Security">Security</option>
              <option value="Other">Other</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Password for Staff *</label>
            <input type="password" name="password" class="form-control" required placeholder="Password they will use to login">
          </div>

          <button type="submit" class="btn btn-primary">Add Staff</button>
          <a href="staff.php" class="btn btn-outline-secondary ms-2">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
