<?php
/**
 * index.php — Landing Page
 * Complaint Management System – by Papa
 */
session_start();

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Welcome';
require 'components/head.php';
?>

<?php require 'components/bg.php'; ?>

<div class="page-wrapper">
  <main class="landing-page">

    <!-- Badge -->
    <div class="landing-badge fade-up">
      Your Voice Matters
    </div>

    <!-- Title -->
    <h1 class="display-title landing-title fade-up delay-1">
      Complaint Management
      <span class="line2">System – by Papa</span>
    </h1>

    <!-- Description -->
    <p class="landing-desc fade-up delay-2">
      A transparent, fast, and secure platform to submit and track
      your complaints. Real change starts with a single report.
    </p>

    <!-- CTA Buttons -->
    <div class="landing-actions fade-up delay-3">
      <a href="login.php" class="btn btn-primary btn-lg">
        🔐 Login
      </a>
      <a href="register.php" class="btn btn-outline btn-lg">
        📝 Create Account
      </a>
    </div>

    <!-- Feature Cards -->
    <div class="landing-features fade-up delay-4">

      <div class="glass-card feature-card">
        <span class="feature-icon">⚡</span>
        <h4>Instant Submission</h4>
        <p>Submit complaints in seconds with image attachments and location details.</p>
      </div>

      <div class="glass-card feature-card">
        <span class="feature-icon">🎯</span>
        <h4>Auto Assignment</h4>
        <p>Complaints are automatically routed to the right staff based on category.</p>
      </div>

      <div class="glass-card feature-card">
        <span class="feature-icon">📊</span>
        <h4>Live Tracking</h4>
        <p>Monitor the status of every complaint you've submitted in real time.</p>
      </div>

    </div>

  </main>
</div>

<!-- Scripts -->
<script src="assets/js/theme.js"></script>
<script src="assets/js/animation.js"></script>

</body>
</html>
