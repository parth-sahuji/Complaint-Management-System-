<?php
/**
 * admin/update.php — Status Update Handler
 */
session_start();

// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: ../login.php');
    exit;
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = (int)($_POST['complaint_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $allowed = ['Submitted', 'Completed'];

    if ($id && in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE complaints SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
}

header('Location: dashboard.php');
exit;
