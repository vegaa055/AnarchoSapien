<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$article = null;
$errors = [];
$success = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $article = $stmt->fetch();

  if (!$article || $article['author_id'] != $_SESSION['user_id']) {
    die("Unauthorized access.");
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    if (empty($title) || empty($content)) {
      $errors[] = "Title and content are required.";
    } else {
      $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

      $update = $pdo->prepare("UPDATE articles SET title = ?, slug = ?, content = ? WHERE id = ?");
      $update->execute([$title, $slug, $content, $_GET['id']]);
      $success = true;

      // Refresh article data
      $stmt->execute([$_GET['id']]);
      $article = $stmt->fetch();
    }
  }
} else {
  die("Invalid article ID.");
}

include('header.php');
?>

<div class="container mt-5">
  <h2>Edit Article</h2>

  <?php if ($success): ?>
    <div class="alert alert-success">Article updated successfully! <a href="view_article.php?id=<?= $article['id'] ?>">View</a></div>
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

  <form method="POST">
    <div class="mb-3">
      <label for="title" class="form-label">Title</label>
      <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
    </div>
    <div class="mb-3">
      <label for="content" class="form-label">Content</label>
      <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($article['content']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-outline-success">Update Article</button>
  </form>
</div>

<?php include('footer.php'); ?>