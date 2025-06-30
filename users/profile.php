<?php
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("User not found");
$user_id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User not found");

include '../includes/header.php';

?>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<div class="container mt-5 text-center">
  <h2 class="profile-title"><?= htmlspecialchars($user['user_name']) ?>'s Profile</h2>
  <img src="/anarchosapien/users/<?= $user['profile_picture'] ?: 'images/default.png' ?>" class="rounded-circle my-3" style="width: 150px; height: 150px; object-fit: cover;">
  <!-- Link to edit_profile.php -->
  <div class="mb-3">
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']): ?>
      <a href="edit_profile.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
    <?php endif; ?>

    <div class="text-start mx-auto" style="max-width: 600px;">
      <h5>About</h5>
      <p><?= nl2br(htmlspecialchars($user['bio'])) ?: '<span class="text-muted">No bio available.</span>' ?></p>
    </div>
  </div>

  <?php include '../includes/footer.php'; ?>