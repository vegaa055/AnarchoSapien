<?php
session_start();
require_once 'db.php';
include('header.php');
?>

<div class="main-content">
  <div class="container">
    <div class="row">
      <div class="col-sm-8 featured-article m-2">
        <h2>Featured Article</h2>
        <?php
        $featured = $pdo->query("SELECT id, title, content, featured_image, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch();
        if ($featured):
        ?>
          <h4><a href="view_article.php?id=<?= $featured['id'] ?>" class="text-decoration-none article-title">
              <?= htmlspecialchars($featured['title']) ?></a></h4>
          <?php if ($featured['featured_image']): ?>
            <img src="<?= htmlspecialchars($featured['featured_image']) ?>" class="img-fluid mb-3" alt="Featured Image">
            <p><small>Published on <?= date("F j, Y", strtotime($featured['created_at'])) ?></small></p>
          <?php endif; ?>
          <hr>
          <p><?= substr(strip_tags($featured['content']), 0, 300) . '...' ?></p>
          <a href="view_article.php?id=<?= $featured['id'] ?>" class="btn btn-outline-success">Read More</a>
        <?php else: ?>
          <p>No featured article found.</p>
        <?php endif; ?>
      </div>
      <div class="article-list col-sm-4 m-2">
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
  </div>

</div>

<?php include('footer.php'); ?>