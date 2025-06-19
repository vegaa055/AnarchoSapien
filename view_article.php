<?php
require_once 'db.php';
session_start();

$article = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT a.*, u.user_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = ?");
    $stmt->execute([$_GET['id']]);
    $article = $stmt->fetch();
}

include('header.php');
?>

<div class="container mt-5">
  <?php if ($article): ?>
    <h1><?= htmlspecialchars($article['title']) ?></h1>
    <p class="text-muted">By <?= htmlspecialchars($article['user_name'] ?? 'Unknown') ?> on <?= $article['created_at'] ?></p>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['author_id']): ?>
      <div class="mb-3">
        <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
        <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article?');">Delete</a>
      </div>
    <?php endif; ?>

    <hr>
    <div class="mt-4">
      <?= nl2br(htmlspecialchars($article['content'])) ?>
    </div>
  <?php else: ?>
    <div class="alert alert-danger">Article not found.</div>
  <?php endif; ?>
</div>

<?php include('footer.php'); ?>
