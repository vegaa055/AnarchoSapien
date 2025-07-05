<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';


$tagStmt = $pdo->query("
  SELECT t.name, COUNT(at.article_id) AS usage_count
  FROM tags t
  LEFT JOIN article_tags at ON t.id = at.tag_id
  GROUP BY t.id
  ORDER BY usage_count DESC, t.name ASC
");
$tags = $tagStmt->fetchAll();
?>

<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<div class="container mt-5">
  <h2 class="section-title-3">All Topics</h2>
  <?php if ($tags): ?>
    <div class="mt-3">
      <?php foreach ($tags as $tag): ?>
        <a href="tag.php?name=<?= urlencode($tag['name']) ?>" class="badge bg-danger text-decoration-none me-2 mb-2 p-2" style="font-size:1.1rem;">
          <?= htmlspecialchars(ucfirst($tag['name'])) ?> <small>(<?= $tag['usage_count'] ?>)</small>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">No tags found.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>