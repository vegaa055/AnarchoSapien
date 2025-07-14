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
    SELECT p.*, u.user_name, u.profile_picture, subscribed_at, bio
    FROM posts p
    JOIN users u ON p.user_id = u.id
    WHERE p.thread_id = ?
    ORDER BY p.created_at ASC
    LIMIT 1
");
$stmt->execute([$thread_id]);
$first_post = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch thread vote score
$stmt = $pdo->prepare("
  SELECT t.*, u.user_name, COALESCE(SUM(v.vote), 0) AS vote_score
  FROM threads t
  JOIN users u ON t.user_id = u.id
  LEFT JOIN thread_votes v ON t.id = v.thread_id
  WHERE t.id = ?
  GROUP BY t.id
");
$stmt->execute([$thread_id]);
$thread = $stmt->fetch(PDO::FETCH_ASSOC);


// Fetch replies
$stmt = $pdo->prepare("
    SELECT p.*, u.user_name, profile_picture, subscribed_at
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
  <div class="view-forum-header">

    <!-- Back button and Title -->
    <a href="../forums/view_forum.php?id=<?= $thread['forum_id'] ?>" class="btn btn-dark mb-3" title="Back to all threads"><i class="fa-solid fa-hand-point-left"></i></a>

    <!-- If user is the author show edit link -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $first_post['user_id']): ?>
      <a href="edit_post.php?id=<?= $first_post['id'] ?>" class="btn btn-link me-2 threads-link-reg float-end">Edit Post</a>
    <?php endif; ?>
    <h2 class="section-title-3 ms-4"><?= htmlspecialchars($thread['title'])  ?></h2>


    <!-- Author and date -->
    <p class="text-primary ms-4">
      <?= date('F j, Y g:i a', strtotime($thread['created_at'])) ?>
    </p>

    <hr class="red-line">
  </div>

  <!-- ─── First Post Section ───────────────── -->
  <div class="first-post text-black p-3 rounded mb-4">
    <div class="row">

      <div class="col-3 user-section-posts">
        <!-- link to user profile through clicking their profile_picture -->
        <a href="/anarchosapien/users/profile.php?id=<?= $first_post['user_id'] ?>"
          class="text-dark"
          target="_blank">
          <!-- ── Profile Picture ───────────────── -->
          <img src="../users/<?= htmlspecialchars($first_post['profile_picture'] ?? '/anarchosapien/images/default-profile.png') ?>"
            alt="Profile Picture"
            class="rounded-circle user-profile-picture "
            style=" width: 120px; height: 120px;">
        </a>

        <!-- ── User Name ──────────────────────── -->
        <!-- link to user profile through clicking their name -->
        <a href="/anarchosapien/users/profile.php?id=<?= $first_post['user_id'] ?>"
          class="text-dark text-decoration-none"
          target="_blank">
          <p class="user-section-name"><?= htmlspecialchars($thread['user_name'] ?? 'Unknown') ?></p>
        </a>
        <hr>

        <p class="user-section-info"><b>Member since:</b> <?= date('F j, Y', strtotime($first_post['subscribed_at'])) ?></p>
        <p class="user-section-info"><?= htmlspecialchars($first_post['bio'] ?? 'No bio available') ?></p>
      </div>
      <div class="col-9 post-content">
        <?= nl2br(htmlspecialchars($first_post['content'])) ?>
        <?php if (isset($_SESSION['user_id'])): ?>
          <?php
          // what did THIS user vote on the thread?
          $threadUserVote = 0;

          $stmt = $pdo->prepare("SELECT vote FROM thread_votes WHERE thread_id = ? AND user_id = ?");
          $stmt->execute([$thread_id, $_SESSION['user_id']]);
          $threadUserVote = $stmt->fetchColumn() ?: 0;

          $threadUp   = $threadUserVote == 1  ? 'up-arrow-filled.png'   : 'up-arrow.png';
          $threadDown = $threadUserVote == -1 ? 'down-arrow-filled.png' : 'down-arrow.png';
          ?>
          <div class="vote-controls">
            <button class="thread-vote-btn border-0"
              data-vote="1"
              data-thread="<?= $thread['id'] ?>">
              <img src="/anarchosapien/images/<?= $threadUp ?>" width="24" height="24">
            </button>

            <span id="vote-score-<?= $thread['id'] ?>"
              class="mx-1 vote-score"><?= $thread['vote_score'] ?></span>

            <button class="thread-vote-btn border-0"
              data-vote="-1"
              data-thread="<?= $thread['id'] ?>">
              <img src="/anarchosapien/images/<?= $threadDown ?>" width="24" height="24">
            </button>
          </div>
          <!-- endif -->
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- ─── Reply Section ───────────────── -->
  <h4 class="section-title-3">Replies</h4>
  <hr class="mb-4">
  <?php foreach ($replies as $reply): ?>
    <?php
    $user_vote = 0;

    if (isset($_SESSION['user_id'])) {
      $stmt = $pdo->prepare("SELECT vote FROM thread_votes WHERE thread_id = ? AND user_id = ?");
      $stmt->execute([$thread_id, $_SESSION['user_id']]);
      $user_vote = $stmt->fetchColumn() ?: 0;
    }
    $post_id = $reply['id'];

    // Total score
    $stmtScore = $pdo->prepare("SELECT COALESCE(SUM(vote),0) FROM post_votes WHERE post_id = ?");
    $stmtScore->execute([$post_id]);
    $postScore = $stmtScore->fetchColumn();

    // User’s vote
    $userVote = 0;
    if (isset($_SESSION['user_id'])) {
      $stmtUV = $pdo->prepare("SELECT vote FROM post_votes WHERE post_id = ? AND user_id = ?");
      $stmtUV->execute([$post_id, $_SESSION['user_id']]);
      $userVote = $stmtUV->fetchColumn() ?: 0;
    }
    ?>

    <div class="mb-3 thread-reply">

      <span class="text-primary"><b>Replied on </b><?= date('F j, Y g:i a', strtotime($reply['created_at'])) ?></span>
      <hr class="text-dark">

      <div class="row">
        <!-- User Profile Picture -->
        <div class="col-2 user-section-posts">

          <!-- link to user profile through clicking their profile_picture -->
          <a href="/anarchosapien/users/profile.php?id=<?= $reply['user_id'] ?>"
            class="text-dark"
            target="_blank">
            <!-- ── Profile Picture ───────────────── -->
            <img src="../users/<?= htmlspecialchars($reply['profile_picture'] ?? '/anarchosapien/images/default-profile.png') ?>"
              alt="Profile Picture"
              class="rounded-circle user-profile-picture"
              style="width: 100px; height: 100px;">
          </a>
          <!-- ── User Name ──────────────────────── -->
          <!-- link to user profile through clicking their name -->
          <a href="/anarchosapien/users/profile.php?id=<?= $reply['user_id'] ?>"
            class="text-dark text-decoration-none"
            target="_blank">
            <p class="user-section-name">
              <span><?= htmlspecialchars($reply['user_name']) ?></span>
            </p>
          </a>
          <small class="text-dark"><b>Member since: </b><?= date('F j, Y', strtotime($reply['subscribed_at'])) ?></small>


        </div>
        <div class="col-10">
          <p class="text-black"><?= nl2br(htmlspecialchars($reply['content'])) ?></p>
          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $reply['user_id']): ?>
            <a href="edit_post.php?id=<?= $reply['id'] ?>" class="btn btn-link edit-delete-link">Edit</a>
            <a href="delete_post.php?id=<?= $reply['id'] ?>" class="btn btn-link edit-delete-link" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
          <?php endif; ?>
          <div class="row mt-4">
            <?php if (isset($_SESSION['user_id'])): ?>
              <?php
              // decide which icon each button gets
              $upIcon   = $userVote == 1  ? 'up-arrow-filled.png'   : 'up-arrow.png';
              $downIcon = $userVote == -1 ? 'down-arrow-filled.png' : 'down-arrow.png';
              ?>
              <div class="post-votes">
                <button class="vote-post-btn border-0"
                  data-post="<?= $post_id ?>"
                  data-vote="1">
                  <img src="/anarchosapien/images/<?= $upIcon ?>" width="20" height="20">
                </button>

                <span id="post-score-<?= $post_id ?>" class="mx-1 text-dark vote-score"><?= $postScore ?></span>

                <button class="vote-post-btn border-0"
                  data-post="<?= $post_id ?>"
                  data-vote="-1">
                  <img src="/anarchosapien/images/<?= $downIcon ?>" width="20" height="20">
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>



      </div>
    </div>
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

<script>
  document.querySelectorAll('.vote-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const threadId = this.dataset.thread;
      const vote = this.dataset.vote;

      fetch('/anarchosapien/threads/vote_thread.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `thread_id=${threadId}&vote=${vote}`
        })
        .then(response => response.text())
        .then(score => {
          document.getElementById(`vote-score-${threadId}`).innerText = score;
          // Clear all vote highlights
          document.querySelectorAll(`.vote-btn[data-thread="${threadId}"]`).forEach(b => b.classList.remove('voted'));

          // Highlight the clicked vote
          this.classList.add('voted');
        });

    });
  });
</script>
<script>
  document.querySelectorAll('.vote-post-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const postId = this.dataset.post;
      const vote = this.dataset.vote;
      fetch('/anarchosapien/threads/vote_post.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}&vote=${vote}`
        })
        .then(res => res.text())
        .then(score => {
          document.getElementById(`post-score-${postId}`).innerText = score;
          document.querySelectorAll(`.vote-post-btn[data-post="${postId}"]`)
            .forEach(b => b.classList.remove('voted'));
          this.classList.add('voted');
        });
      console.log("Voting on post ID:", postId, "vote:", vote);

    });
  });
</script>
<script>
  document.querySelectorAll('.thread-vote-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const threadId = this.dataset.thread;
      const vote = this.dataset.vote;

      const threadBtns = document.querySelectorAll(`.thread-vote-btn[data-thread="${threadId}"]`);
      threadBtns.forEach(b => b.disabled = true);

      fetch('/anarchosapien/threads/vote_thread.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `thread_id=${threadId}&vote=${vote}`
        })
        .then(r => r.text())
        .then(score => {
          document.getElementById(`vote-score-${threadId}`).innerText = score;

          // reset both icons
          threadBtns.forEach(b => {
            const img = b.querySelector('img');
            img.src = (b.dataset.vote == 1) ?
              '/anarchosapien/images/up-arrow.png' :
              '/anarchosapien/images/down-arrow.png';
          });

          // highlight current vote
          const imgClicked = this.querySelector('img');
          imgClicked.src = (vote == 1) ?
            '/anarchosapien/images/up-arrow-filled.png' :
            '/anarchosapien/images/down-arrow-filled.png';
        })
        .finally(() => threadBtns.forEach(b => b.disabled = false));
    });
  });
</script>

<script>
  document.querySelectorAll('.vote-post-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const postId = this.dataset.post;
      const vote = this.dataset.vote;

      // quick disable to stop double-click spamming
      const allBtns = document.querySelectorAll(`.vote-post-btn[data-post="${postId}"]`);
      allBtns.forEach(b => b.disabled = true);

      fetch('/anarchosapien/threads/vote_post.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: `post_id=${postId}&vote=${vote}`
        })
        .then(r => r.text())
        .then(score => {
          // update score
          document.getElementById(`post-score-${postId}`).innerText = score;

          // swap icons: clear both first
          allBtns.forEach(b => {
            const img = b.querySelector('img');
            if (b.dataset.vote == 1)
              img.src = '/anarchosapien/images/up-arrow.png';
            else
              img.src = '/anarchosapien/images/down-arrow.png';
          });

          // highlight the one just clicked
          const imgClicked = this.querySelector('img');
          if (vote == 1)
            imgClicked.src = '/anarchosapien/images/up-arrow-filled.png';
          else
            imgClicked.src = '/anarchosapien/images/down-arrow-filled.png';
        })
        .catch(console.error)
        .finally(() => allBtns.forEach(b => b.disabled = false));
    });
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>