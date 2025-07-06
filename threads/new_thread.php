<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>You must be logged in to create a thread.</div></div>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}

if (!isset($_GET['forum_id']) || !is_numeric($_GET['forum_id'])) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid forum ID.</div></div>";
  include __DIR__ . '/../includes/footer.php';
  exit;
}

$forum_id = $_GET['forum_id'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = trim($_POST['content']);

  if (empty($title) || empty($content)) {
    $errors[] = "Both title and content are required.";
  } else {
    // Create thread
    $stmt = $pdo->prepare("INSERT INTO threads (forum_id, user_id, title) VALUES (?, ?, ?)");
    $stmt->execute([$forum_id, $_SESSION['user_id'], $title]);
    $thread_id = $pdo->lastInsertId();

    // Create first post
    $stmt = $pdo->prepare("INSERT INTO posts (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$thread_id, $_SESSION['user_id'], $content]);

    header("Location: ../threads/view.php?thread_id=$thread_id");
    exit;
  }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <h2>Start a New Thread</h2>

  <?php foreach ($errors as $error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endforeach; ?>

  <form method="POST">
    <div class="mb-3">
      <label for="title" class="form-label">Thread Title</label>
      <input type="text" name="title" id="title" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="content" class="form-label">Message</label>
      <textarea name="content" id="content" class="form-control" rows="8" required></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Post Thread</button>
  </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>