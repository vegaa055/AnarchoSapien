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

?>

<script>
  tinymce.init({
    selector: '#content',
    plugins: 'image link code lists fullscreen',
    toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code fullscreen',
    menubar: false,
    branding: false,
    height: 400,
    automatic_uploads: true,
    images_upload_url: 'upload_image.php?article_id=<?= $article["id"] ?>',
    images_upload_credentials: true,
    setup: function(editor) {
      editor.on('change', function() {
        editor.save();
      });
    }
  });
</script>

<?php

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = $_POST['content'];
  $featuredImage = $article['featured_image'];

  $uploadDir = 'uploads/article_' . $article['id'] . '/';
  if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
  }

  // Replace featured image
  if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['featured_image']['tmp_name'];
    $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
    $safeName = 'featured_' . uniqid() . '.' . strtolower($ext);
    $destPath = $uploadDir . $safeName;

    if (move_uploaded_file($tmp, $destPath)) {
      if (!empty($featuredImage) && file_exists($featuredImage)) {
        unlink($featuredImage);
      }
      $featuredImage = $destPath;
    }
  }

  // Optional embedded image handling
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $safeName = 'embed_' . uniqid() . '.' . strtolower($ext);
    $embedPath = $uploadDir . $safeName;
    if (move_uploaded_file($tmp, $embedPath)) {
      $content = '<img src="' . $embedPath . '" class="img-fluid mb-3">' . $content;
    }
  }

  // Clean unused embedded images
  preg_match_all('/<img\s[^>]*src="([^"]+)"[^>]*>/i', $article['content'], $oldMatches);
  preg_match_all('/<img\s[^>]*src="([^"]+)"[^>]*>/i', $content, $newMatches);

  $oldImages = $oldMatches[1] ?? [];
  $newImages = $newMatches[1] ?? [];

  $unusedImages = array_diff($oldImages, $newImages);
  foreach ($unusedImages as $imgPath) {
    if (str_starts_with($imgPath, 'uploads/') && file_exists($imgPath)) {
      unlink($imgPath);
    }
  }

  if (!empty($title) && !empty($content)) {
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ?, featured_image = ?, updated_at = NOW() WHERE id = ? AND author_id = ?");
    $stmt->execute([$title, $content, $featuredImage, $article['id'], $_SESSION['user_id']]);

    $success = true;

    // Refresh
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

    <div class="mb-3">
      <label for="image" class="form-label">Embed New Image (Optional)</label>
      <input type="file" name="image" id="image" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Update Article</button>
  </form>
</div>

<?php include('footer.php'); ?>