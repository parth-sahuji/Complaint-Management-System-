<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/db.php';

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.name, u.password, r.role_name
            FROM   users u
            JOIN   roles r ON r.id = u.role_id
            WHERE  u.email = ?
            LIMIT  1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role_name'];

            // Regenerate session ID after login to prevent session fixation attacks
            session_regenerate_id(true);

          
            if ($user['role_name'] === 'Admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: dashboard.php');
            }
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

$pageTitle = 'Login';
require 'components/head.php';
?>

<?php require 'components/bg.php'; ?>

<div class="page-wrapper">
  <div class="auth-page">
    <div class="auth-container fade-up">

      <div class="auth-header">
        <div class="auth-logo">📋</div>
        <h2>Welcome back</h2>
        <p>Sign in to your account to continue</p>
      </div>

      <div class="glass-card auth-card">

        <?php if ($error): ?>
          <div data-flash="<?= htmlspecialchars($error) ?>" data-flash-type="error"></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php" novalidate>

          <div class="form-group">
            <input type="email" id="email" name="email" class="form-control"
              placeholder="Email"
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
              required autocomplete="email">
            <label class="floating-label" for="email">Email Address</label>
            <span class="form-error">Please enter a valid email address.</span>
          </div>

          <div class="form-group">
            <div class="input-wrapper">
              <input type="password" id="password" name="password" class="form-control"
                placeholder="Password" required autocomplete="current-password">
              <button type="button" class="toggle-password" title="Show password">👁️</button>
            </div>
            <label class="floating-label" for="password">Password</label>
            <span class="form-error">Password must be at least 6 characters.</span>
          </div>

          <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px;">
            <span class="spinner"></span>
            <span class="btn-text">Sign In &nbsp;→</span>
          </button>

        </form>

      </div>

      <div class="auth-footer">
        Don't have an account? <a href="register.php">Create one free</a>
      </div>

    </div>
  </div>
</div>

<script src="assets/js/theme.js"></script>
<script src="assets/js/animation.js"></script>
<script src="assets/js/validation.js"></script>
</body>
</html>