<?php
/**
 * admin/dashboard.php — Admin Dashboard
 */
session_start();

// Access control — sirf Admin access kar sakta hai
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

// Fetch all complaints with user + category info
$stmt = $pdo->query("
    SELECT
        c.id, c.title, c.description, c.location, c.status, c.created_at,
        cat.category_name,
        u.name  AS user_name,
        u.email AS user_email,
        a.name  AS assigned_name
    FROM   complaints c
    JOIN   categories cat ON cat.id = c.category_id
    JOIN   users u         ON u.id  = c.user_id
    LEFT   JOIN users a    ON a.id  = c.assigned_to
    ORDER  BY c.created_at DESC
");
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch images for each complaint
$complaintIds = array_column($complaints, 'id');
$images = [];
if ($complaintIds) {
    $placeholders = implode(',', array_fill(0, count($complaintIds), '?'));
    $imgStmt = $pdo->prepare("
        SELECT complaint_id, image_path
        FROM   complaint_images
        WHERE  complaint_id IN ($placeholders)
    ");
    $imgStmt->execute($complaintIds);
    foreach ($imgStmt->fetchAll(PDO::FETCH_ASSOC) as $img) {
        $images[$img['complaint_id']][] = $img['image_path'];
    }
}

// Stats
$total     = count($complaints);
$submitted = count(array_filter($complaints, fn($c) => $c['status'] === 'Submitted'));
$completed = count(array_filter($complaints, fn($c) => $c['status'] === 'Completed'));

$activePage = 'admin-dashboard';
$pageTitle  = 'Admin Dashboard';

// head.php ka path admin folder se alag hai
$siteTitle = 'Complaint Management System – by Papa';
$fullTitle  = $pageTitle . ' · ' . $siteTitle;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($fullTitle) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- Animated Background — same as user dashboard -->
<div id="bg-canvas">
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
</div>

<!-- Theme Toggle -->
<button id="themeToggle" class="theme-toggle" title="Toggle theme" aria-label="Toggle dark/light mode">☀️</button>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container"></div>

<div class="page-wrapper">
<div class="dashboard-layout">

  <!-- Sidebar — same structure as user sidebar -->
  <?php
    $userName    = $_SESSION['user_name'] ?? 'Admin';
    $userRole    = $_SESSION['user_role'] ?? 'Admin';
    $userInitial = strtoupper(substr($userName, 0, 1));
  ?>
  <div id="sidebarOverlay" class="sidebar-overlay"></div>
  <aside id="sidebar" class="sidebar" role="navigation" aria-label="Main navigation">
    <div class="sidebar-header">
      <div class="sidebar-logo" aria-hidden="true">📋</div>
      <div class="sidebar-title">CMS <span>Admin</span></div>
      <button id="sidebarToggle" class="sidebar-toggle" title="Collapse sidebar" aria-label="Toggle sidebar">‹</button>
    </div>
    <div class="sidebar-user">
      <div class="user-avatar" aria-hidden="true"><?= htmlspecialchars($userInitial) ?></div>
      <div class="user-info">
        <div class="user-name"><?= htmlspecialchars($userName) ?></div>
        <div class="user-role"><?= htmlspecialchars($userRole) ?></div>
      </div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="nav-item active" title="All Complaints">
        <span class="nav-icon" aria-hidden="true">🏠</span>
        <span class="nav-label">All Complaints</span>
      </a>
    </nav>
    <div class="sidebar-footer">
      <a href="../logout.php" class="nav-item logout" title="Logout">
        <span class="nav-icon" aria-hidden="true">🚪</span>
        <span class="nav-label">Logout</span>
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">

    <!-- Topbar -->
    <header class="topbar">
      <button id="mobileSidebarToggle" class="mobile-toggle" aria-label="Open menu" style="display:none;">☰</button>
      <span class="topbar-title">All Complaints</span>

      <!-- Search -->
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input
          type="text"
          id="searchInput"
          class="search-input"
          placeholder="Search by title, user or category…"
          aria-label="Search complaints"
        >
      </div>

      <div class="topbar-actions">
        <span style="font-size:13px; color:var(--text-secondary);">👑 Admin Panel</span>
      </div>
    </header>

    <!-- Content -->
    <div class="content-area">

      <!-- Stats Row — same as user dashboard -->
      <div class="stats-row mb-24">
        <div class="glass-card stat-card total fade-up">
          <div class="stat-value"><?= $total ?></div>
          <div class="stat-label">Total</div>
        </div>
        <div class="glass-card stat-card submitted fade-up delay-1">
          <div class="stat-value"><?= $submitted ?></div>
          <div class="stat-label">Submitted</div>
        </div>
        <div class="glass-card stat-card completed fade-up delay-2">
          <div class="stat-value"><?= $completed ?></div>
          <div class="stat-label">Completed</div>
        </div>
      </div>

      <!-- Filter Pills — same as user dashboard -->
      <div class="filter-pills mb-24 fade-up delay-1">
        <button class="pill active" data-filter="all">All</button>
        <button class="pill" data-filter="submitted">⏳ Submitted</button>
        <button class="pill" data-filter="completed">✅ Completed</button>
      </div>

      <!-- Complaints Grid — same layout as user dashboard -->
      <div class="complaints-grid" id="complaintsGrid">

        <?php if (empty($complaints)): ?>
          <div class="empty-state" id="emptyState" style="display:flex;flex-direction:column;align-items:center;">
            <span class="empty-icon">📭</span>
            <h3>No complaints yet</h3>
            <p>No complaints have been submitted yet.</p>
          </div>

        <?php else: ?>

          <div class="empty-state" id="emptyState" style="display:none;flex-direction:column;align-items:center;">
            <span class="empty-icon">🔍</span>
            <h3>No results found</h3>
            <p>Try a different search or filter.</p>
          </div>

          <?php foreach ($complaints as $i => $c):
            $statusKey  = strtolower($c['status']);
            $cardImages = $images[$c['id']] ?? [];
            $delay      = min($i, 5);
          ?>
          <article
            class="glass-card complaint-card fade-up delay-<?= $delay ?>"
            data-title="<?= htmlspecialchars(strtolower($c['title'])) ?>"
            data-category="<?= htmlspecialchars(strtolower($c['category_name'])) ?>"
            data-status="<?= htmlspecialchars($statusKey) ?>"
            data-user="<?= htmlspecialchars(strtolower($c['user_name'])) ?>"
            role="button"
            tabindex="0"
            aria-expanded="false"
          >
            <!-- Card Top — same as user dashboard -->
            <div class="complaint-card-inner">
              <div class="complaint-card-top">
                <h3 class="complaint-title"><?= htmlspecialchars($c['title']) ?></h3>
                <span class="expand-chevron" aria-hidden="true">▼</span>
              </div>

              <div class="complaint-meta">
                <span class="badge badge-<?= $statusKey ?>"><?= htmlspecialchars($c['status']) ?></span>
                <span class="meta-chip" data-icon="🏷️"><?= htmlspecialchars($c['category_name']) ?></span>
                <span class="meta-chip" data-icon="👤"><?= htmlspecialchars($c['user_name']) ?></span>
                <span class="meta-chip" data-icon="📅"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
              </div>

              <p class="complaint-preview"><?= htmlspecialchars($c['description']) ?></p>
            </div>

            <!-- Expandable Section — same as user dashboard + admin update form -->
            <div class="complaint-expand">
              <div class="expand-content">

                <p class="expand-section-title">Description</p>
                <p class="expand-description"><?= nl2br(htmlspecialchars($c['description'])) ?></p>

                <p class="expand-section-title">📍 Location</p>
                <p class="expand-location"><?= htmlspecialchars($c['location']) ?></p>

                <p class="expand-section-title">📧 Submitted By</p>
                <p class="expand-location"><?= htmlspecialchars($c['user_name']) ?> &mdash; <?= htmlspecialchars($c['user_email']) ?></p>

                <?php if ($c['assigned_name']): ?>
                <p class="expand-section-title">👤 Assigned To</p>
                <p class="expand-location" style="margin-bottom:18px;"><?= htmlspecialchars($c['assigned_name']) ?></p>
                <?php endif; ?>

                <!-- Images -->
                <p class="expand-section-title">🖼️ Attachments</p>
                <?php if ($cardImages): ?>
                  <div class="expand-images">
                    <?php foreach ($cardImages as $imgPath): ?>
                      <a href="../<?= htmlspecialchars($imgPath) ?>" target="_blank" rel="noopener">
                        <img class="expand-img" src="../<?= htmlspecialchars($imgPath) ?>" alt="Complaint attachment" loading="lazy">
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p class="no-images">No images attached.</p>
                <?php endif; ?>

                <!-- Admin Update Status Form -->
                <p class="expand-section-title" style="margin-top:24px;">⚙️ Update Status</p>
                <form method="POST" action="update.php" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-top:8px;">
                  <input type="hidden" name="complaint_id" value="<?= $c['id'] ?>">
                  <select name="status" class="form-control" style="width:auto; padding:8px 14px; border-radius:10px; background:var(--bg-secondary); color:var(--text-primary); border:1px solid var(--glass-border);">
                    <option value="Submitted" <?= $c['status'] === 'Submitted' ? 'selected' : '' ?>>⏳ Submitted</option>
                    <option value="Completed" <?= $c['status'] === 'Completed' ? 'selected' : '' ?>>✅ Completed</option>
                  </select>
                  <button type="submit" class="btn btn-primary btn-sm">Update</button>
                </form>

              </div>
            </div>
          </article>
          <?php endforeach; ?>

        <?php endif; ?>

      </div><!-- /.complaints-grid -->

    </div><!-- /.content-area -->
  </div><!-- /.main-content -->
</div><!-- /.dashboard-layout -->
</div><!-- /.page-wrapper -->

<script>
// Keyboard accessibility — same as user dashboard
document.querySelectorAll('.complaint-card').forEach(function(card) {
  card.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.classList.toggle('open');
      card.setAttribute('aria-expanded', card.classList.contains('open'));
    }
  });
});
</script>

<script src="../assets/js/theme.js"></script>
<script src="../assets/js/animation.js"></script>
<script src="../assets/js/validation.js"></script>
</body>
</html>
