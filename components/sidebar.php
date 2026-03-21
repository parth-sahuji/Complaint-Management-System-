<?php
/**
 * components/sidebar.php
 * Dashboard sidebar — include inside .dashboard-layout
 * Requires: $activePage = 'dashboard' | 'submit' | ...
 * Requires: session with $_SESSION['user_name'] and $_SESSION['user_role']
 */
$activePage  = $activePage  ?? 'dashboard';
$userName    = $_SESSION['user_name']  ?? 'User';
$userRole    = $_SESSION['user_role']  ?? 'User';
$userInitial = strtoupper(substr($userName, 0, 1));
?>
<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" class="sidebar-overlay"></div>

<aside id="sidebar" class="sidebar" role="navigation" aria-label="Main navigation">

  <!-- Sidebar Header -->
  <div class="sidebar-header">
    <div class="sidebar-logo" aria-hidden="true">📋</div>
    <div class="sidebar-title">
      CMS
      <span>by Papa</span>
    </div>
    <!-- Desktop collapse toggle -->
    <button id="sidebarToggle" class="sidebar-toggle" title="Collapse sidebar" aria-label="Toggle sidebar">‹</button>
  </div>

  <!-- User Info -->
  <div class="sidebar-user">
    <div class="user-avatar" aria-hidden="true"><?= htmlspecialchars($userInitial) ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($userName) ?></div>
      <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
    </div>
  </div>

  <!-- Nav Links -->
  <nav class="sidebar-nav">
    <a href="dashboard.php"
       class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>"
       title="Dashboard">
      <span class="nav-icon" aria-hidden="true">🏠</span>
      <span class="nav-label">Dashboard</span>
    </a>

    <a href="submit.php"
       class="nav-item <?= $activePage === 'submit' ? 'active' : '' ?>"
       title="Submit Complaint">
      <span class="nav-icon" aria-hidden="true">✏️</span>
      <span class="nav-label">Submit Complaint</span>
    </a>
  </nav>

  <!-- Logout -->
  <div class="sidebar-footer">
    <a href="logout.php" class="nav-item logout" title="Logout">
      <span class="nav-icon" aria-hidden="true">🚪</span>
      <span class="nav-label">Logout</span>
    </a>
  </div>

</aside>
