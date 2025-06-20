<?php
require_once 'db.php';
session_start();
include('header.php');

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Unauthorized access.");
}

// Fetch article
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ?");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$article = $stmt->fetch();

if (!$article) {
  die("Article not found or access denied.");
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = $_POST['content'];
  $featuredImage = $article['featured_image']; // keep existing image unless replaced

  // If a new featured image is uploaded, replace it
  if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $imageTmpPath = $_FILES['featured_image']['tmp_name'];
    $imageName = basename($_FILES['featured_image']['name']);
    $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
    $safeName = uniqid() . '.' . strtolower($imageExt);
    $uploadDir = 'uploads/';
    $destPath = $uploadDir . $safeName;

    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    if (move_uploaded_file($imageTmpPath, $destPath)) {
      $featuredImage = $destPath;
    }
  }

  if (!empty($title) && !empty($content)) {
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, featured_image = ?, updated_at = NOW() WHERE id = ? AND author_id = ?");
    $stmt->execute([$title, $content, $featuredImage, $article['id'], $_SESSION['user_id']]);
    $success = true;

    // Refresh article data
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article['id']]);
    $article = $stmt->fetch();
  } else {
    $errors[] = "Title and content are required.";
  }
}
?>

<div class="container mt-5">
  <h2>Edit Article</h2>

  <?php if ($success): ?>
    <div class="alert alert-success">Article updated successfully.</div>
  <?php endif; ?>
  <?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($article['title']) ?>" required>
    </div>

    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea name="content" id="content" rows="10" class="form-control"><?= htmlspecialchars($article['content']) ?></textarea>
    </div>

    <?php if (!empty($article['featured_image'])): ?>
      <div class="mb-3">
        <label class="form-label">Current Featured Image:</label><br>
        <img src="<?= $article['featured_image'] ?>" alt="Featured Image" class="img-fluid rounded mb-2" style="max-width: 300px;">
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label for="featured_image" class="form-label">Replace Featured Image</label>
      <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Update Article</button>
  </form>
</div>

<?php include('footer.php'); ?>