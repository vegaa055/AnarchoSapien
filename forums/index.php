<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
// Start session
session_start();


// Fetch categories and their forums
$categories = $pdo->query("SELECT * FROM forum_categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$categoryForums = [];

foreach ($categories as $category) {
  $stmt = $pdo->prepare("SELECT * FROM forums WHERE category_id = ? ORDER BY name");
  $stmt->execute([$category['id']]);
  $categoryForums[$category['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <h2 class="section-title-2">Forum Index</h2>

  <?php foreach ($categories as $category): ?>
    <!-- ── Forum Card ──────────────────── -->
    <div class="card mb-4 bg-dark">
      <div class="card-header pb-0">
        <img src="../icons/<?= htmlspecialchars($category['icon']) ?>"
          alt="<?= htmlspecialchars($category['name']) ?> icon"
          class="me-2 text-white mb-2" style="width: 28px; height: 28px;">
        <span class="mb-0 text-white section-title-3 h4"><?= htmlspecialchars($category['name']) ?></span>
        <p class="forum-description"><?= htmlspecialchars($category['description']) ?></p>
      </div>

      <!-- Forums -->
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          <?php
          $stmt = $pdo->prepare("SELECT * FROM forums WHERE category_id = ? ORDER BY name");
          $stmt->execute([$category['id']]);
          $forums = $stmt->fetchAll(PDO::FETCH_ASSOC);
          ?>
          <?php foreach ($forums as $forum): ?>
            <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-center ">
              <div>
                <a href="view_forum.php?id=<?= $forum['id'] ?>" class="fw-bold text-decoration-none">
                  <?= htmlspecialchars($forum['name']) ?>
                </a>
                <div class="text-secondary small"><?= htmlspecialchars($forum['description']) ?></div>
              </div>
              <span class="badge bg-secondary">
                Threads: <?= $forum['num_threads'] ?> | Posts: <?= $forum['num_posts'] ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endforeach; ?>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>