<?php
require_once 'db.php';
session_start();  // Start the session to access user data

$article = null;  // Initialize article variable
if (isset($_GET['id']) && is_numeric($_GET['id'])) {  // Check if 'id' is set and is a number
  // Prepare and execute the SQL statement to fetch the article
  $stmt = $pdo->prepare("SELECT a.*, u.user_name FROM articles a LEFT JOIN users u ON a.author_id = u.id WHERE a.id = ?");
  $stmt->execute([$_GET['id']]);
  $article = $stmt->fetch();
}

include('header.php');
?>

<div class="container mt-5 article-header">
  <?php if ($article): ?>
    <h1><?= htmlspecialchars($article['title']) ?></h1>
    <p>By <?= htmlspecialchars($article['user_name'] ?? 'Unknown') ?> on <?= date("F j, Y", strtotime($article['created_at'])) ?></p>

    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['author_id']): ?>
      <div class="mb-3">
        <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
        <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this article?');">Delete</a>
      </div>

    <?php endif; ?>

    <hr>
    <div class="mt-4 article-content">
      <?= $article['content'] ?> <!-- allows HTML like <img> to render -->
    </div>


  <?php else: ?>
    <div class="alert alert-danger">Article not found.</div>
  <?php endif; ?>
</div>

<?php include('footer.php'); ?>