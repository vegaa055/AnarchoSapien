<?php
require_once 'db.php';
session_start();

/* ── 1. Get the article ─────────────────────────────── */
$article = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $stmt = $pdo->prepare(
    "SELECT a.*, u.user_name 
         FROM articles a 
         LEFT JOIN users u ON a.author_id = u.id 
         WHERE a.id = ?"
  );
  $stmt->execute([$_GET['id']]);
  $article = $stmt->fetch();
}

include 'header.php';
?>

<div class="container mt-5">
  <?php if ($article): ?>
    <!-- Article header -->

    <!-- Article Title -->
    <h1 class="article-title-full"><?= htmlspecialchars($article['title']) ?></h1>
    <!-- Featured image -->
    <?php if (!empty($article['featured_image'])): ?>
      <img src="<?= $article['featured_image'] ?>" class="img-fluid mb-3 rounded" alt="featured image">
    <?php endif; ?>
    <!-- Author and date -->
    <p class="text-light author-date">
      By <?= htmlspecialchars($article['user_name'] ?? 'Unknown') ?> • <!-- if no user_name, show 'Unknown' -->
      <?= date('F j, Y', strtotime($article['created_at'])) ?>
    </p>

    <!-- Edit / Delete buttons for author -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['author_id']): ?>
      <div class="mb-3">
        <a href="edit_article.php?id=<?= $article['id'] ?>" class="btn btn-md link-primary">Edit</a>
        <a href="delete_article.php?id=<?= $article['id'] ?>" class="btn btn-md link-danger"
          onclick="return confirm('Delete this article?');">Delete</a>
      </div>
    <?php endif; ?>

    <hr class="red-line"> <!-- New red line -->

    <!-- Article content -->
    <div class="mb-5">
      <?= $article['content'] /* already safe/escaped when inserted */ ?>
    </div>

    <!-- ── Comment form (top-level) ──────────────────── -->
    <h4>Leave a Comment</h4>
    <form method="POST" action="post_comment.php" class="mb-4">
      <input type="hidden" name="article_id" value="<?= $article['id'] ?>">
      <?php if (!isset($_SESSION['user_id'])): ?> <!-- If not logged in, ask for name -->
        <input type="text" name="name" class="form-control mb-2" placeholder="Your name" required>
      <?php endif; ?>
      <textarea name="content" rows="4" class="form-control mb-2" placeholder="Write something..." required></textarea>
      <button type="submit" class="btn btn-secondary">Post Comment</button>
    </form>

    <!-- ── Fetch & render comments tree ───────────────── -->
    <h4 class="mt-5">Comments</h4>
    <?php
    /* 1. Grab all comments for this article, oldest-first                           */
    $cStmt = $pdo->prepare(
      "SELECT c.*, u.user_name 
             FROM comments c 
             LEFT JOIN users u ON c.user_id = u.id 
             WHERE c.article_id = ? 
             ORDER BY c.commented_at ASC"
    );
    $cStmt->execute([$article['id']]);
    $raw = $cStmt->fetchAll(PDO::FETCH_ASSOC);

    /* 2. Bucket by parent_id so we can recurse efficiently                         */
    /* 2. bucket comments by parent_id */
    $byParent = [];

    foreach ($raw as $row) {
      // Treat 0, '' or NULL all the same:
      $parentKey = empty($row['parent_id']) ? null : (int)$row['parent_id'];
      $byParent[$parentKey][] = $row;
    }


    /* 3. Recursive renderer                                                        */
    function renderComments(array $tree, array $byParent, int $level = 0, int $maxIndent = 5)
    {
      if (!isset($tree)) return;
      foreach ($tree as $comment) {
        $indent = min($level, $maxIndent) * 2;  // 2 spaces per level, max 10 spaces
        echo '<div class="ms-' . $indent . ' mb-3 mt-2">';
        echo '<strong>' . htmlspecialchars($comment['user_name'] ?? $comment['name']) . '</strong> ';
        echo '<small class="text-danger">' .
          date('F j, Y H:i', strtotime($comment['commented_at'])) .
          '</small>';
        echo '<p>' . nl2br(htmlspecialchars($comment['content'])) . '</p>';

        // ✅ Show edit/delete buttons for comment owner or admin
        if (
          isset($_SESSION['user_id']) &&
          ($_SESSION['user_id'] == $comment['user_id'] || ($_SESSION['user_is_admin'] ?? false))
        ) {
          echo '<div class="mb-1">';
          echo '<a href="edit_comment.php?id=' . $comment['id'] . '" class="btn btn-sm link-danger text-decoration-none me-1">Edit</a>';
          echo '<a href="delete_comment.php?id=' . $comment['id'] . '" class="btn btn-sm link-danger text-decoration-none" onclick="return confirm(\'Delete this comment?\');">Delete</a>';
          echo '</div>';
        }

        $articleId = htmlspecialchars($comment['article_id']);
        include 'reply_form_partial.php';

        if (isset($byParent[$comment['id']])) {
          renderComments($byParent[$comment['id']], $byParent, $level + 1, $maxIndent);
        }
        echo '</div>';
      }
    }


    if (isset($byParent[null])) {
      renderComments($byParent[null], $byParent);
    } else {
      echo '<p class="text-muted">No comments yet.</p>';
    }
    ?>

  <?php else: ?>
    <div class="alert alert-danger">Article not found.</div>
  <?php endif; ?>
</div>

<?php include 'footer.php'; ?>