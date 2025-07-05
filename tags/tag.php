<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';


if (!isset($_GET['name']) || empty($_GET['name'])) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>No tag specified.</div></div>";
  include __DIR__ . '/includes/footer.php';
  exit;
}

$tagName = trim($_GET['name']);

$stmt = $pdo->prepare("
  SELECT a.id, a.title, a.slug, a.created_at, a.content
  FROM articles a
  INNER JOIN article_tags at ON a.id = at.article_id
  INNER JOIN tags t ON t.id = at.tag_id
  WHERE t.name = ?
  ORDER BY a.created_at DESC
");
$stmt->execute([$tagName]);
$articles = $stmt->fetchAll();
?>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<div class="container mt-5">
  <h2>Articles tagged with: <em><?= htmlspecialchars($tagName) ?></em></h2>
  <?php if ($articles): ?>
    <ul class="list-group mt-3">
      <?php foreach ($articles as $article): ?>
        <li class="list-group-item tag-list-item">
          <a href="../articles/view.php?id=<?= $article['id'] ?>">
            <?= htmlspecialchars($article['title']) ?>
          </a>
          <small class="text-secondary"> | <?= date("F j, Y", strtotime($article['created_at'])) ?></small>
          <!-- show first few sentences of article -->
          <p class="tagged-article-preview"><?= substr(strip_tags($article['content']), 0, 300) . '...' ?></p>
          <p></p>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="alert alert-warning mt-3">No articles found for this tag.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>