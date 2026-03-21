<?php
/**
 * register.php — User Registration
 */
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// ── PHP Register Logic ──────────────────
$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';

    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $phone    = trim($_POST['phone']    ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check duplicate email
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, phone, role_id)
                VALUES (?, ?, ?, ?, 3)
            ");
            $stmt->execute([$name, $email, $hash, $phone ?: null]);
            $success = 'Account created! You can now log in.';
        }
    }
}

$pageTitle = 'Register';
require 'components/head.php';
?>

<?php require 'components/bg.php'; ?>

<div class="page-wrapper">
  <div class="auth-page">
    <div class="auth-container fade-up">

      <!-- Header -->
      <div class="auth-header">
        <div class="auth-logo">🚀</div>
        <h2>Create an account</h2>
        <p>Join us and start submitting complaints</p>
      </div>

      <!-- Card -->
      <div class="glass-card auth-card">

        <?php if ($error):   ?><div data-flash="<?= htmlspecialchars($error)   ?>" data-flash-type="error"></div><?php endif; ?>
        <?php if ($success): ?><div data-flash="<?= htmlspecialchars($success) ?>" data-flash-type="success"></div><?php endif; ?>

        <form id="registerForm" method="POST" action="register.php" novalidate>

          <!-- Name -->
          <div class="form-group">
            <input
              type="text"
              id="name"
              name="name"
              class="form-control"
              placeholder="Full Name"
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
              required
              autocomplete="name"
            >
            <label class="floating-label" for="name">Full Name</label>
            <span class="form-error">Please enter your full name.</span>
          </div>

          <!-- Email -->
          <div class="form-group">
            <input
              type="email"
              id="email"
              name="email"
              class="form-control"
              placeholder="Email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required
              autocomplete="email"
            >
            <label class="floating-label" for="email">Email Address</label>
            <span class="form-error">Please enter a valid email address.</span>
          </div>

          <!-- Password -->
          <div class="form-group">
            <div class="input-wrapper">
              <input
                type="password"
                id="password"
                name="password"
                class="form-control"
                placeholder="Password"
                required
                autocomplete="new-password"
              >
              <button type="button" class="toggle-password" title="Show password">👁️</button>
            </div>
            <label class="floating-label" for="password">Password</label>
            <span class="form-error">Password must be at least 6 characters.</span>
            <span class="form-hint">Minimum 6 characters</span>
          </div>

          <!-- Phone -->
          <div class="form-group">
            <input
              type="tel"
              id="phone"
              name="phone"
              class="form-control"
              placeholder="Phone"
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
              autocomplete="tel"
            >
            <label class="floating-label" for="phone">Phone Number (optional)</label>
            <span class="form-error">Please enter a valid phone number.</span>
          </div>

          <!-- Submit -->
          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
            <span class="spinner"></span>
            <span class="btn-text">Create Account &nbsp;→</span>
          </button>

        </form>

      </div>

      <!-- Footer -->
      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in</a>
      </div>

    </div>
  </div>
</div>

<script src="assets/js/theme.js"></script>
<script src="assets/js/animation.js"></script>
<script src="assets/js/validation.js"></script>
</body>
</html>
