<?php
session_start();
require_once 'db.php';
include('header.php');
?>

<div class="main-content">
  <div class="featured-article">
    <h2>Featured Article</h2>
    <?php
    $featured = $pdo->query("SELECT id, title, content, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch();
    if ($featured):
    ?>
      <h4><a href="view_article.php?id=<?= $featured['id'] ?>" class="text-decoration-none text-light"><?= htmlspecialchars($featured['title']) ?></a></h4>
      <p><small>Published on <?= $featured['created_at'] ?></small></p>
      <p><?= nl2br(substr($featured['content'], 0, 300)) ?>...</p>
      <a href="view_article.php?id=<?= $featured['id'] ?>" class="btn btn-outline-success">Read More</a>
    <?php else: ?>
      <p>No featured article found.</p>
    <?php endif; ?>
  </div>

  <div class="article-list">
    <h3>Recent Posts</h3>
    <ul>
      <?php
      $stmt = $pdo->query("SELECT id, title FROM articles ORDER BY created_at DESC LIMIT 10 OFFSET 1");
      while ($row = $stmt->fetch()):
      ?>
        <li><a href="view_article.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></li>
      <?php endwhile; ?>
    </ul>
  </div>
</div>

<?php include('footer.php'); ?>
