<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = trim($_POST['content']);

  if (empty($title) || empty($content)) {
    $errors[] = "Title and content are required.";
  } else {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    $stmt = $pdo->prepare("INSERT INTO articles (title, slug, content, author_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $_SESSION['user_id']]);
    $success = true;
  }
}
?>

<?php include('header.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Article - AnarchoSapien</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles/style.css">
</head>
<body class="bg-dark text-white">
  <div class="container mt-5 create-article">
    <h2>Create New Article</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">Article published successfully!</div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
      </div>
      <div class="mb-3">
        <label for="content" class="form-label">Content</label>
        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
      </div>
      <button type="submit" class="btn btn-outline-success">Publish Article</button>
    </form>
  </div>
</body>

<?php include('footer.php'); ?>
</html>
