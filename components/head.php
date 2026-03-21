<?php
/**
 * components/head.php
 * Include at the top of every page:
 *   $pageTitle = "Login";
 *   require 'components/head.php';
 */
$siteTitle = 'Complaint Management System – by Papa';
$fullTitle  = isset($pageTitle) ? $pageTitle . ' · ' . $siteTitle : $siteTitle;
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($fullTitle) ?></title>
  <meta name="description" content="Online Complaint Management System – by Papa">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">

  <!-- Main CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
