<?php
if (!isset($article['id'])) return;

$stmt = $pdo->prepare("
  SELECT t.name 
  FROM tags t
  INNER JOIN article_tags at ON t.id = at.tag_id
  WHERE at.article_id = ?
  ORDER BY t.name
");
$stmt->execute([$article['id']]);
$tags = $stmt->fetchAll();

if ($tags):
?>
  <div class="mt-3">
    <strong>Tags:</strong>
    <?php foreach ($tags as $tag): ?>
      <a href="../tags/tag.php?name=<?= urlencode($tag['name']) ?>" class="badge bg-danger text-decoration-none me-1 mb-4 p-2" style="font-size:1.1rem;;">
        <?= htmlspecialchars($tag['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>