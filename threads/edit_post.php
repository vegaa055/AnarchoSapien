<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
  die("Invalid post ID.");
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post || $_SESSION['user_id'] != $post['user_id']) {
  die("Unauthorized.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = trim($_POST['content']);
  if (!empty($content)) {
    $stmt = $pdo->prepare("UPDATE posts SET content = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$content, $post_id]);
    header("Location: view.php?thread_id=" . $post['thread_id']);
    exit;
  }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container mt-5">
  <h2>Edit Post</h2>
  <form method="POST">
    <div class="mb-3">
      <textarea name="content" class="form-control" rows="6"><?= htmlspecialchars($post['content']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save</button>
    <a href="view.php?thread_id=<?= $post['thread_id'] ?>" class="btn btn-secondary">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>