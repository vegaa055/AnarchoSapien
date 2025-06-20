<?php
require_once 'db.php';
session_start();
include('header.php');

if (!isset($_SESSION['user_id'])) {   // Check if the user is logged in
  die("Unauthorized access.");
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']); 
  $content = $_POST['content'];   
  $featuredImage = null;

  // Featured image upload
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
  

  // Optional: embed image in content
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $embedTmpPath = $_FILES['image']['tmp_name'];
    $embedName = basename($_FILES['image']['name']);
    $embedExt = pathinfo($embedName, PATHINFO_EXTENSION);
    $embedSafe = uniqid() . '.' . strtolower($embedExt);
    $embedDest = 'uploads/' . $embedSafe;
    move_uploaded_file($embedTmpPath, $embedDest);
    $content = '<img src="' . $embedDest . '" class="img-fluid mb-3">' . $content;
  }

  if (!empty($title) && !empty($content)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $stmt = $pdo->prepare("INSERT INTO articles (title, slug, featured_image, content, author_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $featuredImage, $content, $_SESSION['user_id']]);
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

  <!-- Form for creating a new article -->
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" name="title" id="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea name="content" id="content" rows="8" class="form-control"></textarea>
    </div>
    <div class="mb-3">
      <label for="featured_image" class="form-label">Featured Image</label>
      <input type="file" name="featured_image" id="featured_image" class="form-control" accept="image/*">
    </div>
    <button type="submit" class="btn btn-primary">Publish</button>
  </form>
</div>

<?php include('footer.php'); ?>