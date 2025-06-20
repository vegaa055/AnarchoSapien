<?php
require_once 'db.php';
session_start();
include('header.php');

if (!isset($_SESSION['user_id'])) {
  die("Unauthorized access.");
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = $_POST['content'];

  // Image handling
  if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $imageTmpPath = $_FILES['image']['tmp_name'];
    $imageName = basename($_FILES['image']['name']);
    $imageExt = pathinfo($imageName, PATHINFO_EXTENSION);
    $safeName = uniqid() . '.' . strtolower($imageExt);
    $uploadDir = 'uploads/';
    $destPath = $uploadDir . $safeName;

    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }

    move_uploaded_file($imageTmpPath, $destPath);

    $content = '<img src="' . $destPath . '" class="img-fluid mb-3">' . $content;
  }

  if (!empty($title) && !empty($content)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, author_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $_SESSION['user_id']]);
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
      <textarea name="content" id="content" rows="8" class="form-control" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Publish</button>
  </form>
</div>

<?php include('footer.php'); ?>