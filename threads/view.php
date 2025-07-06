<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();


if (!isset($_GET['thread_id']) || !is_numeric($_GET['thread_id'])) {
  die("Invalid thread ID.");
}

$thread_id = $_GET['thread_id'];

// Fetch thread info
$stmt = $pdo->prepare("
  SELECT t.*, u.user_name
  FROM threads t
  JOIN users u ON t.user_id = u.id
  WHERE t.id = ?
");
$stmt->execute([$thread_id]);
$thread = $stmt->fetch();

if (!$thread) {
  die("Thread not found.");
}


// Handle reply submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
  $content = trim($_POST['content']);
  if ($content) {
    $stmt = $pdo->prepare("INSERT INTO posts (thread_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$thread_id, $_SESSION['user_id'], $content]);

    // Optional: redirect to self to prevent form resubmission
    header("Location: view.php?thread_id=$thread_id");
    exit;
  } else {
    $errors[] = "Reply content cannot be empty.";
  }
}
// Fetch first post
$stmt = $pdo->prepare("
    SELECT p.*, u.user_name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.thread_id = ?
    ORDER BY p.created_at ASC
    LIMIT 1
");
$stmt->execute([$thread_id]);
$first_post = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch replies
$stmt = $pdo->prepare("
    SELECT p.*, u.user_name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.thread_id = ?
    ORDER BY p.created_at ASC
    LIMIT 1000 OFFSET 1
");
$stmt->execute([$thread_id]);
$replies = $stmt->fetchAll();



require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-5">
  <!-- back to view_forum.php?id{forum_id} -->
  <a href="../forums/view_forum.php?id=<?= $thread['forum_id'] ?>" class="btn btn-dark mb-3" title="Back to all threads">&#8617;</a>
  <h2 class="section-title-3"><?= htmlspecialchars($thread['title'])  ?></h2>

  <p class="text-primary">By <?= htmlspecialchars($thread['user_name'] ?? 'Unknown') ?> on <?= date('F j, Y H:i', strtotime($thread['created_at'])) ?> <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $first_post['user_id']): ?>
      <a href="edit_post.php?id=<?= $first_post['id'] ?>" class="btn btn-link me-2 threads-link-reg">Edit</a>
      <!-- <a href="delete_post.php?id=<?= $first_post['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a> -->
    <?php endif; ?>
  </p>
  <hr class="red-line">


  <div class="bg-dark text-white p-3 rounded mb-4">
    <?= nl2br(htmlspecialchars($first_post['content'])) ?>
  </div>

  <h4 class="section-title-3">Replies</h4>
  <hr class="mb-4">
  <?php foreach ($replies as $reply): ?>
    <div class="mb-3">
      <strong><?= htmlspecialchars($reply['user_name']) ?></strong>
      <small class="text-primary"><?= date('F j, Y H:i', strtotime($reply['created_at'])) ?></small>
      <p><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
    </div>
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
      <a href="edit_post.php?id=<?= $reply['id'] ?>" class="btn btn-sm btn-outline-warning me-2">Edit</a>
      <a href="delete_post.php?id=<?= $reply['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if (isset($_SESSION['user_id'])): ?>
    <h5 class="section-title-sm mt-4">Post a Reply</h5>
    <?php foreach ($errors as $error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>
    <form method="POST">
      <textarea name="content" rows="4" class="form-control mb-2" required></textarea>
      <button type="submit" class="btn btn-primary">Submit Reply</button>
    </form>
  <?php else: ?>
    <div class="alert alert-info">Please log in to reply to this thread.</div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>