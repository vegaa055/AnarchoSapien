<?php
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

include __DIR__ . '/../includes/render_comments.php';

/* ── 1. Get the article ─────────────────────────────── */
$article = null;
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $stmt = $pdo->prepare(
    "SELECT a.*, u.user_name, u.profile_picture
         FROM articles a 
         LEFT JOIN users u ON a.author_id = u.id 
         WHERE a.id = ?"
  );
  $stmt->execute([$_GET['id']]);
  $article = $stmt->fetch();
}
include __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />

<div class="container mt-5">
  <?php if ($article): ?>
    <!-- ── Article header ─────────────────────────────── -->

    <!-- ── Article Title ──────────────────────────── -->
    <h1 class="article-title-full"><?= htmlspecialchars($article['title']) ?></h1>
    <!-- ── Featured image ──────────────────── -->
    <?php if (!empty($article['featured_image'])): ?>
      <img src="../articles/<?= htmlspecialchars($article['featured_image']) ?>" alt="Featured image" class="img-fluid mb-3">
    <?php endif; ?>
    <!-- ── Author and date ──────────────────── -->
    <p class="text-light author-date">
      <!-- ── show pfp of author ─────── -->
      <a href="/anarchosapien/users/profile.php?id=<?= $article['author_id'] ?>" target="_blank">
        <img src="../users/<?= htmlspecialchars($article['profile_picture'] ?: 'default.png') ?>" class="rounded-circle me-2" style="width: 48px; height: 48px; object-fit: cover;">
      </a>
      <!-- ── show author name and date ─────── -->
      By <?= htmlspecialchars($article['user_name'] ?? 'Unknown') ?> • <!-- if no user_name, show 'Unknown' -->
      <?= date('F j, Y', strtotime($article['created_at'])) ?>
    </p>

    <?php
    $liked = false;
    $likeCount = 0;

    if (isset($article['id'])) {
      // Get like count
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM article_likes WHERE article_id = ?");
      $stmt->execute([$article['id']]);
      $likeCount = $stmt->fetchColumn();

      // Check if current user liked it
      if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT 1 FROM article_likes WHERE article_id = ? AND user_id = ?");
        $stmt->execute([$article['id'], $_SESSION['user_id']]);
        $liked = $stmt->fetch() !== false;
      }
    }
    ?>

    <div class="mb-3 d-flex align-items-center gap-2">
      <button id="articleLikeBtn"
        class="btn btn-sm <?= $liked ? 'btn-danger' : 'btn-outline-danger' ?>"
        data-liked="<?= $liked ? '1' : '0' ?>"
        title="<?= $liked ? 'Unlike this article' : 'Like this article' ?>">
        <i class="<?= $liked ? 'fa-solid' : 'fa-regular' ?> fa-thumbs-up"></i>
      </button>
      <span id="articleLikeCount" class="text-secondary"><?= $likeCount ?> like<?= $likeCount == 1 ? '' : 's' ?></span>
    </div>


    <!-- ── Edit / Delete buttons for author ──────────────────── -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['author_id']): ?>
      <div class="mb-3">
        <a href="<?= BASE_URL ?>edit.php?id=<?= $article['id'] ?>" class="btn btn-md link-primary">Edit</a>
        <a href="<?= BASE_URL ?>delete.php?id=<?= $article['id'] ?>" class="btn btn-md link-danger"
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
    <form method="POST" action="/anarchosapien/comments/post_comment.php" class="mb-4">
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

    /* 1. Grab all comments for this article, oldest-first */
    // Fetch all comments with user info, ordered by commented_at
    $stmt = $pdo->prepare("SELECT c.*, u.user_name, u.profile_picture FROM comments c LEFT JOIN users u ON c.user_id = u.id WHERE c.article_id = ? ORDER BY c.commented_at ASC");
    $stmt->execute([$article['id']]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize comments by parent_id
    $byParent = [];
    foreach ($comments as $comment) {
      // Treat null, 0, and "0" as top-level
      $parentId = ($comment['parent_id'] === null || $comment['parent_id'] == 0) ? 0 : (int)$comment['parent_id'];
      $byParent[$parentId][] = $comment;
    }

    // Sort all buckets (top-level and replies) by commented_at (oldest to newest)
    foreach ($byParent as &$children) {
      usort($children, function ($a, $b) {
        return strtotime($a['commented_at']) <=> strtotime($b['commented_at']);
      });
    }
    unset($children);

    // Render comments
    if (isset($byParent[0])) {
      renderComments($byParent[0], $byParent);
    } else {
      echo '<p class="text-muted">No comments yet.</p>';
    }
    ?>

  <?php else: ?>
    <div class="alert alert-danger">Article not found.</div>
  <?php endif; ?>
</div>

<script>
  document.getElementById('articleLikeBtn')?.addEventListener('click', function() {
    const btn = this;
    const articleId = <?= $article['id'] ?>;

    fetch('../comments/toggle_like.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'article_id=' + articleId
      })
      .then(res => res.json())
      .then(data => {
        const icon = btn.querySelector('i');
        const countSpan = document.getElementById('articleLikeCount');

        btn.classList.toggle('btn-danger', data.status === 'liked');
        btn.classList.toggle('btn-outline-danger', data.status !== 'liked');

        icon.classList.toggle('fa-solid', data.status === 'liked');
        icon.classList.toggle('fa-regular', data.status !== 'liked');

        countSpan.textContent = data.likes + ' like' + (data.likes !== 1 ? 's' : '');
      });
  });
</script>

<script>
  document.getElementById('likeBtn')?.addEventListener('click', function(e) {
    e.preventDefault();
    const btn = this;
    const articleId = <?= $article['id'] ?>;

    fetch('<?= BASE_URL ?>comments/toggle_like.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'article_id=' + articleId
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === 'liked') {
          btn.classList.remove('btn-outline-danger');
          btn.classList.add('btn-danger');
          btn.innerHTML = '❤️ Unlike';
        } else if (data.status === 'unliked') {
          btn.classList.remove('btn-danger');
          btn.classList.add('btn-outline-danger');
          btn.innerHTML = '❤️ Like';
        }
        document.getElementById('likeCount').textContent = data.likes + ' like' + (data.likes === 1 ? '' : 's');
      });
  });
</script>

<script>
  document.querySelectorAll('.like-comment').forEach(btn => {
    btn.addEventListener('click', () => {
      const commentId = btn.dataset.id;

      fetch('<?= BASE_URL ?>../comments/toggle_comment_like.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: 'comment_id=' + commentId
        })
        .then(res => res.json())
        .then(data => {
          const countSpan = document.getElementById('like-count-' + commentId);
          countSpan.textContent = data.likes + ' like' + (data.likes != 1 ? 's' : '');

          btn.classList.toggle('btn-danger', data.status === 'liked');
          btn.classList.toggle('btn-outline-danger', data.status !== 'liked');

          const icon = btn.querySelector('i');
          icon.classList.toggle('fa-solid', data.status === 'liked');
          icon.classList.toggle('fa-regular', data.status !== 'liked');
        });

    });
  });
</script>



<?php include __DIR__ . '/../includes/footer.php'; ?>