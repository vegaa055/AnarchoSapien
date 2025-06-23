<?php
require_once 'db.php';
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
    header("Location: view_article.php?id=" . $comment['article_id']);
    exit;
  }
}
?>

<?php include 'header.php'; ?>
<div class="container mt-5">
  <h4>Edit Your Comment</h4>
  <form method="POST">
    <textarea name="content" class="form-control mb-3" rows="5" required><?= htmlspecialchars($comment['content']) ?></textarea>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="view_article.php?id=<?= $comment['article_id'] ?>" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php include 'footer.php'; ?>