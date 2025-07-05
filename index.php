<?php
session_start();
require_once __DIR__ . '/includes/config.php'; // Include the configuration file
require_once __DIR__ . '/includes/db.php';
include __DIR__ . '/includes/header.php';
?>

<div class="main-content">
  <div class="container">
    <div class="row">
      <div class="col-sm-8 featured-article m-2">
        <h2>Featured Article</h2>
        <hr class="text-danger">
        <?php
        $featured = $pdo->query("SELECT id, title, content, featured_image, created_at FROM articles ORDER BY created_at DESC LIMIT 1")->fetch();
        if ($featured):
        ?>
          <!-- Display featured article title -->
          <h4><a href="<?= BASE_URL ?>articles/view.php?id=<?= $featured['id'] ?>" class="text-decoration-none article-title">
              <?= htmlspecialchars($featured['title']) ?></a></h4>
          <?php if ($featured['featured_image']): ?>

            <!-- Display featured image if available - onclick directs us to article -->
            <a href="<?= BASE_URL ?>articles/view.php?id=<?= $featured['id'] ?>" class="text-decoration-none article-title">
              <img src="articles/<?= htmlspecialchars($featured['featured_image']) ?>" class="img-fluid mb-3" alt="Featured Image">
            </a>

            <p><small>Published on <?= date("F j, Y", strtotime($featured['created_at'])) ?></small></p>
          <?php endif; ?>
          <hr>
          <p><?= substr(strip_tags($featured['content']), 0, 300) . '...' ?></p>
          <a href="<?= BASE_URL ?>articles/view.php?id=<?= $featured['id'] ?>" class="btn btn-outline-danger">Read More</a>
        <?php else: ?>
          <p>No featured article found.</p>
        <?php endif; ?>
      </div>
      <div class="article-list col-sm-4 m-2">
        <h3>Recent Posts</h3>
        <hr class="text-danger">
        <ul>
          <?php
          $stmt = $pdo->query("SELECT id, title, created_at FROM articles ORDER BY created_at DESC LIMIT 10 OFFSET 1");
          while ($row = $stmt->fetch()):
          ?>
            <li><a href="<?= BASE_URL ?>articles/view.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?>
              </a>
              <small class="text-secondary">| <?= date("F j, Y", strtotime($row['created_at'])) ?></small>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    </div>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>