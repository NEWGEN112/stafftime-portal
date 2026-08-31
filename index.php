<?php
// StaffTime - Formal Public Homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>StaffTime | Digital Staff Attendance for Schools</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --navy: #0b3d91;
      --navy-dark: #082c6b;
      --gold: #c9a227;
      --light: #f4f6f9;
    }
    body {
      background: var(--light);
      color: #1f2937;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }
    .topbar {
      background: #fff;
      border-bottom: 1px solid #e5e7eb;
    }
    .brand {
      color: var(--navy);
      font-weight: 700;
      letter-spacing: 0.3px;
      text-decoration: none;
    }
    .hero {
      background: linear-gradient(135deg, var(--navy) 0%, var(--navy-dark) 100%);
      color: #fff;
      padding: 72px 0 80px;
    }
    .hero-badge {
      display: inline-block;
      background: rgba(201, 162, 39, 0.2);
      color: #f5e6a8;
      border: 1px solid rgba(201, 162, 39, 0.45);
      padding: 6px 14px;
      border-radius: 50px;
      font-size: 0.85rem;
      margin-bottom: 18px;
    }
    .hero h1 {
      font-weight: 700;
      line-height: 1.2;
    }
    .hero .lead {
      color: rgba(255,255,255,0.9);
      max-width: 620px;
    }
    .btn-gold {
      background: var(--gold);
      border: none;
      color: #1a1a1a;
      font-weight: 600;
    }
    .btn-gold:hover {
      background: #b8911f;
      color: #1a1a1a;
    }
    .btn-outline-light-custom {
      border: 1.5px solid rgba(255,255,255,0.75);
      color: #fff;
      font-weight: 500;
    }
    .btn-outline-light-custom:hover {
      background: #fff;
      color: var(--navy);
    }
    .section-title {
      color: var(--navy);
      font-weight: 700;
    }
    .feature-card {
      background: #fff;
      border: 1px solid #e8ecf1;
      border-radius: 14px;
      padding: 28px 22px;
      height: 100%;
      transition: 0.2s ease;
    }
    .feature-card:hover {
      box-shadow: 0 10px 28px rgba(11, 61, 145, 0.08);
      transform: translateY(-2px);
    }
    .feature-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      background: rgba(11, 61, 145, 0.08);
      color: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.35rem;
      margin-bottom: 14px;
    }
    .step-num {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: var(--navy);
      color: #fff;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .cta-box {
      background: #fff;
      border: 1px solid #e8ecf1;
      border-radius: 16px;
      padding: 40px 24px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.04);
    }
    footer {
      background: #fff;
      border-top: 1px solid #e5e7eb;
      color: #6b7280;
      font-size: 0.9rem;
    }
  </style>
</head>
<body>

  <!-- Top bar -->
  <div class="topbar py-3">
    <div class="container d-flex justify-content-between align-items-center">
      <a href="index.php" class="brand fs-5">StaffTime</a>
      <div class="d-flex gap-2">
        <a href="public/login.php" class="btn btn-sm btn-outline-primary">Login</a>
        <a href="public/register.php" class="btn btn-sm btn-primary">Register School</a>
      </div>
    </div>
  </div>

  <!-- Hero -->
  <section class="hero">
    <div class="container text-center text-md-start">
      <div class="row align-items-center">
        <div class="col-lg-8 mx-auto text-center">
          <div class="hero-badge">Built for African Schools</div>
          <h1 class="display-5 mb-3">Digital Staff Attendance,<br>Done the Professional Way</h1>
          <p class="lead mb-4 mx-auto">
            Replace paper timebooks with a secure, mobile-friendly system for
            check-in, late tracking, leave management, and term reports.
          </p>
          <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="public/register.php" class="btn btn-gold btn-lg px-4">Register Your School</a>
            <a href="public/login.php" class="btn btn-outline-light-custom btn-lg px-4">Staff / Admin Login</a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features -->
  <section class="py-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title h3">Everything Your School Needs</h2>
        <p class="text-muted">Simple tools for administrators and teaching staff</p>
      </div>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">✓</div>
            <h5 class="mb-2">Staff Check-In &amp; Check-Out</h5>
            <p class="text-muted mb-0">Teachers mark attendance on their phones. Clear daily records for the school office.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">⏱</div>
            <h5 class="mb-2">Late &amp; Absent Tracking</h5>
            <p class="text-muted mb-0">Automatic late status and end-of-day absent marking based on your school times.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">▤</div>
            <h5 class="mb-2">Term &amp; Session Reports</h5>
            <p class="text-muted mb-0">Generate attendance summaries by term or full session for admin use.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">✉</div>
            <h5 class="mb-2">Leave Management</h5>
            <p class="text-muted mb-0">Staff apply for leave. Admin reviews and approves in one place.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">☰</div>
            <h5 class="mb-2">Export &amp; Backup</h5>
            <p class="text-muted mb-0">Download staff lists and attendance records as CSV for Excel.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">⌂</div>
            <h5 class="mb-2">Multi-School Ready</h5>
            <p class="text-muted mb-0">Each school registers separately and manages only its own staff.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How it works -->
  <section class="py-5 bg-white border-top border-bottom">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="section-title h3">How It Works</h2>
      </div>
      <div class="row g-4 text-center">
        <div class="col-md-4">
          <div class="step-num">1</div>
          <h5>Register Your School</h5>
          <p class="text-muted">Create an admin account in a few minutes.</p>
        </div>
        <div class="col-md-4">
          <div class="step-num">2</div>
          <h5>Add Your Staff</h5>
          <p class="text-muted">Admin adds teachers with phone and password.</p>
        </div>
        <div class="col-md-4">
          <div class="step-num">3</div>
          <h5>Run Daily Attendance</h5>
          <p class="text-muted">Staff check in each morning. Reports stay ready.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="py-5">
    <div class="container">
      <div class="cta-box text-center">
        <h2 class="section-title h3 mb-3">Ready for your school?</h2>
        <p class="text-muted mb-4">Professional staff attendance — simple setup, built for schools.</p>
        <a href="public/register.php" class="btn btn-primary btn-lg px-4 me-2 mb-2">Register Your School</a>
        <a href="public/login.php" class="btn btn-outline-primary btn-lg px-4 mb-2">Login</a>
      </div>
    </div>
  </section>

  <footer class="py-4">
    <div class="container text-center">
      <strong class="text-dark">StaffTime</strong> &nbsp;·&nbsp;
      Digital Staff Timebook for Schools &nbsp;·&nbsp;
      &copy; <?php echo date('Y'); ?>
    </div>
  </footer>

</body>
</html>
