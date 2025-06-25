<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
  die("User not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bio = trim($_POST['bio']);
  $profile_picture = $user['profile_picture'];

  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/users/";
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
    $fileName = 'user_' . $user_id . '_' . uniqid() . '.' . strtolower($ext);
    $path = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $path)) {
      if ($profile_picture && file_exists($profile_picture) && $profile_picture !== 'images/default.png') {
        unlink($profile_picture);
      }
      $profile_picture = $path;
    }
  }

  $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_picture = ? WHERE id = ?");
  $stmt->execute([$bio, $profile_picture, $user_id]);

  header("Location: profile.php?id=$user_id");
  exit;
}

include 'header.php';
?>

<div class="container mt-5" style="max-width: 600px;">
  <h3>Edit Profile</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Profile Picture</label><br>
      <img src="<?= $user['profile_picture'] ?: 'images/default.png' ?>" class="rounded mb-2" style="width: 100px; height: 100px; object-fit: cover;">
      <input type="file" name="profile_picture" class="form-control">
    </div>
    <div class="mb-3">
      <label class="form-label">About Me</label>
      <textarea name="bio" rows="5" class="form-control"><?= htmlspecialchars($user['bio']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>
</div>

<?php include 'footer.php'; ?>