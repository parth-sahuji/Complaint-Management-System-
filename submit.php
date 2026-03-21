<?php
/**
 * submit.php — Submit a New Complaint
 */
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/db.php';

$userId  = $_SESSION['user_id'];
$error   = '';
$success = '';

// ── PHP Submission Logic ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $categoryId  = (int)($_POST['category']   ?? 0);
    $location    = trim($_POST['location']    ?? '');

    if (empty($title) || empty($description) || !$categoryId || empty($location)) {
        $error = 'Please fill in all required fields.';
    } elseif (empty($_FILES['images']['name'][0])) {
        $error = 'Please upload at least 1 image.';
    } else {
        // Validate category exists
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $catStmt->execute([$categoryId]);
        if (!$catStmt->fetch()) {
            $error = 'Invalid category selected.';
        } else {
            // Auto-assign: pick a staff/admin user round-robin (example: first staff member)
            $staffStmt = $pdo->prepare("
                SELECT u.id FROM users u
                JOIN   roles r ON r.id = u.role_id
                WHERE  r.role_name IN ('Admin','Staff')
                ORDER  BY RAND()
                LIMIT  1
            ");
            $staffStmt->execute();
            $assignedTo = $staffStmt->fetchColumn();

            if (!$assignedTo) {
                // Fallback to current user if no staff exist yet
                $assignedTo = $userId;
            }

            // Insert complaint
            $insStmt = $pdo->prepare("
                INSERT INTO complaints (user_id, category_id, assigned_to, title, description, location)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insStmt->execute([$userId, $categoryId, $assignedTo, $title, $description, $location]);
            $complaintId = $pdo->lastInsertId();

            // Handle image uploads (max 2)
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $uploaded = 0;
            $fileCount = min(count($_FILES['images']['name']), 2);

            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $tmpName  = $_FILES['images']['tmp_name'][$i];
                $origName = basename($_FILES['images']['name'][$i]);
                $ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

                // Validate type
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed)) continue;

                // Check MIME
                $mimeType = mime_content_type($tmpName);
                if (!str_starts_with($mimeType, 'image/')) continue;

                // Max 5MB
                if ($_FILES['images']['size'][$i] > 5 * 1024 * 1024) continue;

                $newName  = 'complaint_' . $complaintId . '_' . ($i + 1) . '_' . time() . '.' . $ext;
                $destPath = $uploadDir . $newName;

                if (move_uploaded_file($tmpName, $destPath)) {
                    $imgStmt = $pdo->prepare("
                        INSERT INTO complaint_images (complaint_id, image_path)
                        VALUES (?, ?)
                    ");
                    $imgStmt->execute([$complaintId, $destPath]);
                    $uploaded++;
                }
            }

            if ($uploaded === 0) {
                // Rollback complaint if no images saved (edge case)
                $pdo->prepare("DELETE FROM complaints WHERE id = ?")->execute([$complaintId]);
                $error = 'Image upload failed. Please try valid image files (JPG, PNG, WEBP).';
            } else {
                header('Location: dashboard.php?submitted=1');
                exit;
            }
        }
    }
}

// Fetch categories
$categories = $pdo->query("SELECT id, category_name FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

$activePage = 'submit';
$pageTitle  = 'Submit Complaint';
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
      <span class="topbar-title">Submit a Complaint</span>
      <div class="topbar-actions">
        <a href="dashboard.php" class="btn btn-outline btn-sm">← Back</a>
      </div>
    </header>

    <!-- Content -->
    <div class="content-area">
      <div class="submit-page">

        <div class="submit-header fade-up">
          <h2>New Complaint</h2>
          <p>Describe your issue clearly. Our team will be assigned automatically.</p>
        </div>

        <?php if ($error): ?>
          <div data-flash="<?= htmlspecialchars($error) ?>" data-flash-type="error"></div>
        <?php endif; ?>

        <div class="glass-card submit-card fade-up delay-1">

          <form
            id="complaintForm"
            method="POST"
            action="submit.php"
            enctype="multipart/form-data"
            novalidate
          >

            <!-- Section: Basic Info -->
            <span class="section-label">Basic Information</span>

            <!-- Title -->
            <div class="form-group">
              <input
                type="text"
                id="title"
                name="title"
                class="form-control"
                placeholder="Title"
                value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                required
                maxlength="200"
              >
              <label class="floating-label" for="title">Complaint Title</label>
              <span class="form-error">Please enter a title for your complaint.</span>
            </div>

            <!-- Category -->
            <div class="form-group">
              <select
                id="category"
                name="category"
                class="form-control <?= isset($_POST['category']) && $_POST['category'] ? 'has-value' : '' ?>"
                required
              >
                <option value="" disabled <?= empty($_POST['category']) ? 'selected' : '' ?>>Select category</option>
                <?php foreach ($categories as $cat): ?>
                  <option
                    value="<?= $cat['id'] ?>"
                    <?= (isset($_POST['category']) && $_POST['category'] == $cat['id']) ? 'selected' : '' ?>
                  ><?= htmlspecialchars($cat['category_name']) ?></option>
                <?php endforeach; ?>
              </select>
              <label class="floating-label" for="category">Category</label>
              <span class="form-error">Please select a category.</span>
            </div>

            <!-- Description -->
            <div class="form-group">
              <textarea
                id="description"
                name="description"
                class="form-control"
                placeholder="Description"
                required
                rows="5"
              ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
              <label class="floating-label" for="description">Detailed Description</label>
              <span class="form-error">Please describe your complaint.</span>
            </div>

            <!-- Section: Location -->
            <span class="section-label">Location Details</span>

            <!-- Location -->
            <div class="form-group">
              <textarea
                id="location"
                name="location"
                class="form-control"
                placeholder="Location"
                required
                rows="3"
              ><?= htmlspecialchars($_POST['location'] ?? '') ?></textarea>
              <label class="floating-label" for="location">Full Address / Location</label>
              <span class="form-hint">Provide as much detail as possible (building, street, landmark, city)</span>
              <span class="form-error">Please provide the location.</span>
            </div>

            <!-- Section: Images -->
            <span class="section-label">Attachments</span>

            <!-- Upload Zone -->
            <div id="uploadZone" class="image-upload-zone" role="button" tabindex="0" aria-label="Upload images">
              <span class="upload-icon">🖼️</span>
              <p class="upload-title">Click to upload or drag & drop</p>
              <p class="upload-hint">JPG, PNG, WEBP · Max 5 MB each · Min 1, Max 2 images</p>
            </div>
            <input
              type="file"
              id="imageInput"
              name="images[]"
              accept="image/jpeg,image/png,image/gif,image/webp"
              multiple
            >

            <!-- Preview Grid -->
            <div id="imagePreviews" class="image-previews"></div>
            <p id="imageCounter" class="image-counter">0 / 2 images selected</p>

            <!-- Submit -->
            <div style="margin-top: 32px;">
              <button type="submit" class="btn btn-primary btn-lg btn-full">
                <span class="spinner"></span>
                <span class="btn-text">🚀 &nbsp;Submit Complaint</span>
              </button>
              <p style="text-align:center;margin-top:14px;font-size:0.82rem;color:var(--text-muted);">
                Once submitted, complaints cannot be edited or deleted.
              </p>
            </div>

          </form>

        </div><!-- /.submit-card -->
      </div><!-- /.submit-page -->
    </div><!-- /.content-area -->
  </div><!-- /.main-content -->
</div><!-- /.dashboard-layout -->
</div><!-- /.page-wrapper -->

<script>
// Show success toast if redirected back with ?submitted=1
const params = new URLSearchParams(window.location.search);
if (params.get('submitted') === '1') {
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
      showToast('Your complaint has been submitted successfully! 🎉', 'success');
    }, 500);
  });
}
</script>

<script src="assets/js/theme.js"></script>
<script src="assets/js/animation.js"></script>
<script src="assets/js/validation.js"></script>
</body>
</html>
