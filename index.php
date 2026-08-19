<?php
// StaffTime - Public Homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StaffTime - Digital Staff Timebook for Schools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8fafc; }
    .hero {
      background: linear-gradient(135deg, #0d6efd, #0a58ca);
      color: white;
      padding: 70px 20px;
      border-radius: 0 0 24px 24px;
    }
    .feature-card {
      border: none;
      border-radius: 16px;
      box-shadow: 0 4px 16px rgba(0,0,0,0.06);
      height: 100%;
    }
    .feature-icon {
      font-size: 2rem;
    }
  </style>
</head>
<body>

  <!-- Hero -->
  <div class="hero text-center">
    <div class="container">
      <h1 class="display-5 fw-bold mb-3">StaffTime</h1>
      <p class="lead mb-4">
        Digital Staff Timebook for African Schools.<br>
        Attendance, leaves, reports — simple and mobile-friendly.
      </p>
      <div class="d-flex justify-content-center gap-2 flex-wrap">
        <a href="public/login.php" class="btn btn-light btn-lg px-4">Login</a>
        <a href="public/register.php" class="btn btn-outline-light btn-lg px-4">Register Your School</a>
      </div>
    </div>
  </div>

  <!-- Features -->
  <div class="container py-5">
    <h3 class="text-center mb-4">What StaffTime Does</h3>
    <div class="row g-4">

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">✅</div>
          <h5>Staff Check-In / Check-Out</h5>
          <p class="text-muted mb-0">Teachers mark attendance digitally. No more paper timebooks.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">⏰</div>
          <h5>Late & Absent Tracking</h5>
          <p class="text-muted mb-0">Automatic late marking and end-of-day absent marking.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">📄</div>
          <h5>PDF & CSV Reports</h5>
          <p class="text-muted mb-0">Download term/session reports and export data for Excel.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">🏖️</div>
          <h5>Leave Management</h5>
          <p class="text-muted mb-0">Staff apply for leave. Admin approves or rejects.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">📱</div>
          <h5>Mobile Friendly</h5>
          <p class="text-muted mb-0">Works well on phones — perfect for school admins and teachers.</p>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card feature-card p-4 text-center">
          <div class="feature-icon mb-2">🏫</div>
          <h5>Multi-School Ready</h5>
          <p class="text-muted mb-0">Each school registers separately and manages its own staff.</p>
        </div>
      </div>

    </div>
  </div>

  <!-- How it works -->
  <div class="bg-white py-5">
    <div class="container">
      <h3 class="text-center mb-4">How It Works</h3>
      <div class="row g-4 text-center">
        <div class="col-md-4">
          <div class="p-3">
            <h1 class="text-primary">1</h1>
            <h5>Register Your School</h5>
            <p class="text-muted">Create an admin account in minutes.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3">
            <h1 class="text-primary">2</h1>
            <h5>Add Your Staff</h5>
            <p class="text-muted">Admin adds teachers with phone and password.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3">
            <h1 class="text-primary">3</h1>
            <h5>Start Daily Attendance</h5>
            <p class="text-muted">Staff check in each morning. Reports are ready anytime.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- CTA -->
  <div class="container py-5 text-center">
    <h3 class="mb-3">Ready for your school?</h3>
    <p class="text-muted mb-4">Start free. Simple setup. Built for African schools.</p>
    <a href="public/register.php" class="btn btn-primary btn-lg px-5">Register Your School</a>
    <div class="mt-3">
      <a href="public/login.php" class="text-decoration-none">Already registered? Login</a>
    </div>
  </div>

  <footer class="text-center text-muted py-4 border-top">
    <small>StaffTime &copy; <?php echo date('Y'); ?> — Digital Staff Timebook for Schools</small>
  </footer>

</body>
</html>
