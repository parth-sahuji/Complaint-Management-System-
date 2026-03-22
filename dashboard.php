<?php
/**
 * dashboard.php — User Dashboard
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$userId = $_SESSION['user_id'];

// Fetch complaints for this user
$stmt = $pdo->prepare("
    SELECT
        c.id, c.title, c.description, c.location, c.status, c.created_at,
        cat.category_name,
        u.name AS assigned_name
    FROM   complaints c
    JOIN   categories cat ON cat.id = c.category_id
    LEFT   JOIN users u   ON u.id  = c.assigned_to
    WHERE  c.user_id = ?
    ORDER  BY c.created_at DESC
");
$stmt->execute([$userId]);
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
$total      = count($complaints);
$submitted  = count(array_filter($complaints, fn($c) => $c['status'] === 'Submitted'));
$inprogress = count(array_filter($complaints, fn($c) => $c['status'] === 'In Progress'));
$completed  = count(array_filter($complaints, fn($c) => $c['status'] === 'Completed'));

$activePage = 'dashboard';
$pageTitle  = 'Dashboard';
require 'components/head.php';
?>

<?php require 'components/bg.php'; ?>

<div class="page-wrapper">
<div class="dashboard-layout">

  <?php require 'components/sidebar.php'; ?>

  <!-- Main Content -->
  <div class="main-content" id="mainContent">

    <!-- Topbar -->
    <header class="topbar">
      <button id="mobileSidebarToggle" class="mobile-toggle" aria-label="Open menu" style="display:none;">☰</button>
      <span class="topbar-title">My Complaints</span>

      <!-- Search -->
      <div class="search-bar">
        <span class="search-icon">🔍</span>
        <input
          type="text"
          id="searchInput"
          class="search-input"
          placeholder="Search by title or category…"
          aria-label="Search complaints"
        >
      </div>

      <!-- Actions -->
      <div class="topbar-actions">
        <a href="submit.php" class="btn btn-primary btn-sm">✏️ New Complaint</a>
      </div>
    </header>

    <!-- Content -->
    <div class="content-area">

      <!-- Stats Row — 4 stats -->
      <div class="stats-row mb-24" style="grid-template-columns: repeat(4, 1fr);">
        <div class="glass-card stat-card total fade-up">
          <div class="stat-value"><?= $total ?></div>
          <div class="stat-label">Total</div>
        </div>
        <div class="glass-card stat-card submitted fade-up delay-1">
          <div class="stat-value"><?= $submitted ?></div>
          <div class="stat-label">Submitted</div>
        </div>
        <div class="glass-card stat-card fade-up delay-2" style="border-color: rgba(245,158,11,0.3);">
          <div class="stat-value" style="color: #f59e0b;"><?= $inprogress ?></div>
          <div class="stat-label">In Progress</div>
        </div>
        <div class="glass-card stat-card completed fade-up delay-3">
          <div class="stat-value"><?= $completed ?></div>
          <div class="stat-label">Completed</div>
        </div>
      </div>

      <!-- Filter Pills — In Progress add kiya -->
      <div class="filter-pills mb-24 fade-up delay-1">
        <button class="pill active" data-filter="all">All</button>
        <button class="pill" data-filter="submitted">⏳ Submitted</button>
        <button class="pill" data-filter="in-progress">🔄 In Progress</button>
        <button class="pill" data-filter="completed">✅ Completed</button>
      </div>

      <!-- Complaints Grid -->
      <div class="complaints-grid" id="complaintsGrid">

        <?php if (empty($complaints)): ?>
          <div class="empty-state" id="emptyState" style="display:flex;flex-direction:column;align-items:center;">
            <span class="empty-icon">🚀</span>
            <h3>No complaints yet</h3>
            <p>You haven't submitted any complaints. Your voice matters — let's change that.</p>
            <a href="submit.php" class="btn btn-primary">✏️ Submit Your First Complaint</a>
          </div>

        <?php else: ?>

          <div class="empty-state" id="emptyState" style="display:none;flex-direction:column;align-items:center;">
            <span class="empty-icon">🔍</span>
            <h3>No results found</h3>
            <p>Try a different search or filter.</p>
          </div>

          <?php foreach ($complaints as $i => $c):
            $statusKey  = strtolower(str_replace(' ', '-', $c['status']));
            $cardImages = $images[$c['id']] ?? [];
            $delay      = min($i, 5);
          ?>
          <article
            class="glass-card complaint-card fade-up delay-<?= $delay ?>"
            data-title="<?= htmlspecialchars(strtolower($c['title'])) ?>"
            data-category="<?= htmlspecialchars(strtolower($c['category_name'])) ?>"
            data-status="<?= htmlspecialchars($statusKey) ?>"
            role="button"
            tabindex="0"
            aria-expanded="false"
          >
            <!-- Card Top (always visible) -->
            <div class="complaint-card-inner">
              <div class="complaint-card-top">
                <h3 class="complaint-title"><?= htmlspecialchars($c['title']) ?></h3>
                <span class="expand-chevron" aria-hidden="true">▼</span>
              </div>

              <div class="complaint-meta">
                <span class="badge badge-<?= $statusKey ?>"><?= htmlspecialchars($c['status']) ?></span>
                <span class="meta-chip" data-icon="🏷️"><?= htmlspecialchars($c['category_name']) ?></span>
                <span class="meta-chip" data-icon="📅"><?= date('d M Y', strtotime($c['created_at'])) ?></span>
              </div>

              <p class="complaint-preview"><?= htmlspecialchars($c['description']) ?></p>
            </div>

            <!-- Expandable Section -->
            <div class="complaint-expand">
              <div class="expand-content">

                <p class="expand-section-title">Description</p>
                <p class="expand-description"><?= nl2br(htmlspecialchars($c['description'])) ?></p>

                <p class="expand-section-title">📍 Location</p>
                <p class="expand-location"><?= htmlspecialchars($c['location']) ?></p>

                <?php if ($c['assigned_name']): ?>
                <p class="expand-section-title">👤 Assigned To</p>
                <p class="expand-location" style="margin-bottom:18px;"><?= htmlspecialchars($c['assigned_name']) ?></p>
                <?php endif; ?>

                <p class="expand-section-title">🖼️ Attachments</p>
                <?php if ($cardImages): ?>
                  <div class="expand-images">
                    <?php foreach ($cardImages as $imgPath): ?>
                      <a href="<?= htmlspecialchars($imgPath) ?>" target="_blank" rel="noopener">
                        <img
                          class="expand-img"
                          src="<?= htmlspecialchars($imgPath) ?>"
                          alt="Complaint attachment"
                          loading="lazy"
                        >
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <p class="no-images">No images attached.</p>
                <?php endif; ?>

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

<script src="assets/js/theme.js"></script>
<script src="assets/js/animation.js"></script>
<script src="assets/js/validation.js"></script>
</body>
</html>
