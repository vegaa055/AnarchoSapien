<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Missing comment ID");
$comment_id = (int) $_GET['id'];

// Fetch comment
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) die("Comment not found");

// Authorization: user must be owner or admin
if (
  !isset($_SESSION['user_id']) ||
  ($_SESSION['user_id'] != $comment['user_id'] && !($_SESSION['user_is_admin'] ?? false))
) {
  die("Unauthorized");
}

// Update on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content']);
  if ($content !== '') {
    $stmt = $pdo->prepare("UPDATE comments SET content = ? WHERE id = ?");
    $stmt->execute([$content, $comment_id]);
    header("Location: /anarchosapien/articles/view.php?id=" . $comment['article_id']);
    exit;
  }
}
?>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container mt-5">
  <h4>Edit Your Comment</h4>
  <form method="POST">
    <textarea name="content" class="form-control mb-3" rows="5" required><?= htmlspecialchars($comment['content']) ?></textarea>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="/anarchosapien/articles/view.php?id=<?= $comment['article_id'] ?>" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>