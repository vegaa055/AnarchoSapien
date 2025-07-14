<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/header.php';

$forumId = $_GET['id'] ?? null;

if (!$forumId || !is_numeric($forumId)) {
  echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid forum ID.</div></div>";
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

// Fetch forum info
$stmt = $pdo->prepare("SELECT * FROM forums WHERE id = ?");
$stmt->execute([$forumId]);
$forum = $stmt->fetch();

if (!$forum) {
  echo "<div class='container mt-5'><div class='alert alert-warning'>Forum not found.</div></div>";
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

// Fetch threads
$threadStmt = $pdo->prepare("
  SELECT t.id, t.title, t.created_at, u.user_name, user_id,
         (SELECT COUNT(*) FROM posts p WHERE p.thread_id = t.id) AS reply_count,
         (SELECT MAX(created_at) FROM posts WHERE thread_id = t.id) AS last_post_time
  FROM threads t
  LEFT JOIN users u ON t.user_id = u.id
  WHERE t.forum_id = ?
  ORDER BY t.created_at DESC
");
$threadStmt->execute([$forumId]);
$threads = $threadStmt->fetchAll();
?>

<div class="container mt-4">
  <!-- back to view.php -->
  <div class="view-forum-header">
    <a href="../forums/index.php" class="btn btn-dark mb-3" title="Back to all forums"><i class="fa-solid fa-hand-point-left"></i></a>

    <!-- Only show the New Thread button if user is registered and logged in -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id']): ?>
      <a href="../threads/new_thread.php?forum_id=<?= $forumId ?>" class="btn btn-success float-end"><i class="fa-solid fa-plus"></i>New Thread</a>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center">
      <h3 class="section-title-2 ms-4"><?= htmlspecialchars($forum['name']) ?></h3>
    </div>
    <h4 class="section-title-3 mb-4 ms-4"><?= htmlspecialchars($forum['description']) ?></h4>
    <hr>
  </div>
  <?php if ($threads): ?>
    <ul class="list-group">
      <?php foreach ($threads as $thread): ?>
        <li class="list-group-item list-group-item-dark d-flex justify-content-between align-items-start pb-3">
          <div>
            <a href="../threads/view.php?thread_id=<?= $thread['id'] ?>" class="fw-bold text-decoration-none">
              <?= htmlspecialchars($thread['title']) ?>
            </a>
            <div class="text-muted small">
              Started by
              <a href="/anarchosapien/users/profile.php?id=<?= $thread['user_id'] ?>"
                class="text-dark"
                target="_blank">
                <?= htmlspecialchars($thread['user_name'] ?? 'Unknown') ?>
              </a>
              on <?= date("F j, Y, g:i a", strtotime($thread['created_at'])) ?>

              <!-- Link to started by user's profile -->

            </div>
          </div>
          <div class="text-end small">
            <div><?= $thread['reply_count'] ?> replies</div>
            <div>Last post: <?= $thread['last_post_time'] ? date("M j, Y", strtotime($thread['last_post_time'])) : 'No replies' ?></div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <div class="alert alert-info mt-3">No threads yet. Be the first to post!</div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>