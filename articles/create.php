<?php
// load configuration and database connection
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
session_start();
if (!isset($_SESSION['user_id'])) {
  die("Unauthorized access.");
}

$errors = [];
$success = false;

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
    images_upload_url: '../users/upload_image.php?draft=1',

    images_upload_credentials: true,
    setup: function(editor) {
      editor.on('change', function() {
        editor.save();
      });
    }
  });
</script>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = $_POST['content'];
  $featuredImage = null;

  if (!empty($title) && !empty($content)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, author_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, '', $_SESSION['user_id']]);
    $articleId = $pdo->lastInsertId();

    $draftDir = "uploads/draft_user_" . $_SESSION['user_id'] . "/";
    $articleDir = "uploads/article_$articleId/";

    if (!file_exists($articleDir)) {
      mkdir($articleDir, 0755, true);
    }

    if (file_exists($draftDir)) {
      $files = glob($draftDir . '*');
      $usedImages = [];

      preg_match_all('/<img\s[^>]*src="([^"]+)"[^>]*>/i', $content, $matches);
      $usedImages = $matches[1] ?? [];

      // Move images from draft to article directory
      foreach ($files as $file) {
        $filename = basename($file);
        $dest = $articleDir . $filename;

        if (in_array($file, $usedImages)) { // if image is used in content 
          rename($file, $dest);
          $content = str_replace($file, $dest, $content); // update content with new path
        } else {
          unlink($file); // remove unused image
        }
      }
      @rmdir($draftDir);
    }

    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
      $tmp = $_FILES['featured_image']['tmp_name'];
      $ext = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
      $safeName = 'featured_' . uniqid() . '.' . strtolower($ext);
      $destPath = $articleDir . $safeName;

      if (move_uploaded_file($tmp, $destPath)) {
        $featuredImage = $destPath;
      }
    }

    $stmt = $pdo->prepare("UPDATE articles SET content = ?, featured_image = ? WHERE id = ?");
    $stmt->execute([$content, $featuredImage, $articleId]);

    $success = true;
  } else {
    $errors[] = "Title and content are required.";
  }
}
?>

<div class="container mt-5">
  <h2>Create New Article</h2>
  <?php if ($success): ?>
    <div class="alert alert-success">Article posted successfully.</div>
  <?php endif; ?>
  <?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endforeach; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" name="title" id="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea name="content" id="content" rows="10" class="form-control" required></textarea>
    </div>

    <div class="mb-3">
      <label for="featured_image" class="form-label">Featured Image (Optional)</label>
      <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
    </div>

    <button type="submit" class="btn btn-primary">Publish</button>
  </form>
</div>

<?php include('../includes/footer.php'); ?>