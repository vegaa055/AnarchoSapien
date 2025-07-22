<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

function renderReplies(array $parentBucket, array $byParent, int $level = 0)
{
  if (!isset($parentBucket) || $level > 3) return;          // stop at 4 levels

  foreach ($parentBucket as $post) {
    $indent = $level * 3;  // Bootstrap ms‑* uses 0‑5 steps
?>

    <div class="ms-<?= $indent ?> mb-3 ps-0 thread-reply">

      <div class="container">
        <div class="row">
          <span class="text-primary replied-on-row">Replied on <?= date('F j, Y g:i a', strtotime($post['created_at'])) ?></span>
        </div>

        <div class="row">
          <div class="col-sm-2 user-section-posts p-3">
            <!-- ── Profile Picture ───────────────── -->
            <div class="row">
              <a href="/anarchosapien/users/profile.php?id=<?= $post['user_id'] ?>"
                target="_blank">
                <img src="../users/<?= htmlspecialchars($post['profile_picture'] ?? '/anarchosapien/images/default-profile.png') ?>"
                  alt="Profile Picture"
                  class="rounded-circle user-profile-picture">
              </a>
            </div>
            <!-- ── User Name ──────────────────────── -->
            <div class="row">
              <a href="/anarchosapien/users/profile.php?id=<?= $post['user_id'] ?>"
                class="text-decoration-none"
                target="_blank">
                <p class="user-section-name">
                  <span><?= htmlspecialchars($post['user_name']) ?></span>
                </p>
              </a>
            </div>
            <!-- ── User Quote ──────────────────────── -->
            <div class="row">
              <p class="user-section-quote"><?= htmlspecialchars($reply['quote'] ?? '') ?></p>
            </div>
            <!-- ── User Info ──────────────────────── -->
            <div class="row">
              <small class="user-joined-on"><b>Member since: </b><?= date('F j, Y', strtotime($post['subscribed_at'])) ?></small>
            </div>
          </div>


          <div class="col-lg-10 post-content">
            <!-- ── Post Content ──────────────────────── -->
            <div class="row">
              <p class=" post-reply-content"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
            </div>
            <div class="row">
              <!-- reply button (if logged in) -->
              <?php if (isset($_SESSION['user_id'])): ?>
                <button class="btn btn-link reply-btn text-end" data-thread="<?= $post['thread_id'] ?>"
                  data-parent="<?= $post['id'] ?>">Reply</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>


<?php
    // recurse into children
    if (isset($byParent[$post['id']])) {
      renderReplies($byParent[$post['id']], $byParent, $level + 1);
    }
  }
}

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
// Handle (top‑level or nested) reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
  $content   = trim($_POST['content']);
  $parent_id = isset($_POST['parent_id']) && is_numeric($_POST['parent_id'])
    ? (int)$_POST['parent_id'] : null;

  if ($content) {
    $stmt = $pdo->prepare("
            INSERT INTO posts (thread_id, parent_id, user_id, content)
            VALUES (?, ?, ?, ?)");
    $stmt->execute([$thread_id, $parent_id, $_SESSION['user_id'], $content]);

    header("Location: view.php?thread_id=$thread_id");
    exit;
  }
  $errors[] = "Reply content cannot be empty.";
}

// Fetch first post
$stmt = $pdo->prepare("
    SELECT p.*, u.user_name, u.profile_picture, subscribed_at, bio, quote
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


// all posts for this thread, oldest first
$stmt = $pdo->prepare("
    SELECT p.*, u.user_name, profile_picture, quote, subscribed_at
    FROM posts p
    JOIN users u ON u.id = p.user_id
    WHERE p.thread_id = ?
    ORDER BY p.created_at ASC
");
$stmt->execute([$thread_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// bucket by parent_id
$byParent = [];
foreach ($rows as $row) {
  $byParent[$row['parent_id']][] = $row;
}



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


    <!-- date -->
    <p class="text-primary ms-4">
      <?= date('F j, Y g:i a', strtotime($thread['created_at'])) ?>
    </p>

    <hr class="red-line">
  </div>

  <!-- ─── First Post Section ───────────────── -->
  <div class="first-post text-black p-3 rounded mb-4">
    <div class="row">

      <!-- ─── User Info Section ───────────────── -->
      <div class="col-sm user-section-posts p-3 rounded">

        <div class="row">
          <a href="/anarchosapien/users/profile.php?id=<?= $first_post['user_id'] ?>"
            class="text-dark"
            target="_blank">
            <!-- ── Profile Picture ───────────────── -->
            <img src="../users/<?= htmlspecialchars($first_post['profile_picture'] ?? '/anarchosapien/images/default-profile.png') ?>"
              alt="Profile Picture"
              class="rounded-circle user-profile-picture ">
          </a>
        </div>
        <!-- ── User Name ──────────────────────── -->
        <div class="row">
          <a href="/anarchosapien/users/profile.php?id=<?= $first_post['user_id'] ?>"
            class="text-dark text-decoration-none"
            target="_blank">
            <p class="user-section-name"><?= htmlspecialchars($thread['user_name'] ?? 'Unknown') ?></p>
          </a>
        </div>

        <!-- ── User Info ──────────────────────── -->
        <div class="row">
          <p class="user-section-info"><b>Member since:</b> <?= date('F j, Y', strtotime($first_post['subscribed_at'])) ?></p>
        </div>
        <!-- ── User Bio ──────────────────────── -->
        <div class="row">
          <p class="user-section-quote"><?= htmlspecialchars($first_post['quote'] ?? '') ?></p>
        </div>
      </div>

      <div class="col-lg-9 post-content">

        <?= nl2br(htmlspecialchars($first_post['content'])) ?>


      </div>
      <div class="row">
        <!-- ── Voting Controls - Authentication ──────────────────────── -->
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
        <?php endif; ?>
      </div>

    </div>


  </div>

  <!-- ─── Reply Section ───────────────── -->
  <div class="container replies-title-container">
    <h4 class="section-title-3 replies-title">Replies</h4>
  </div>

  <hr class="mb-4">
  <?php
  if (isset($byParent[null])) {
    renderReplies($byParent[null], $byParent);
  } else {
    echo "<p class='text-muted'>No replies yet.</p>";
  }
  ?>
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
    <div class="alert alert-info">Please <a href="/anarchosapien/users/login.php">login</a> or <a href="/anarchosapien/users/register.php">register</a> to reply to this thread.</div>
  <?php endif; ?>
  <form id="reply-form" class="d-none" method="POST">
    <input type="hidden" name="parent_id" id="parent_id">
    <textarea name="content" id="reply-content" rows="4"
      class="form-control mb-2" placeholder="Write a reply…" required></textarea>
    <button type="submit" class="btn btn-primary">Post reply</button>
    <button type="button" id="cancel-reply" class="btn btn-secondary ms-2">Cancel</button>
  </form>

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
<script>
  document.querySelectorAll('.reply-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const parentId = this.dataset.parent;
      const container = this.closest('.thread-reply'); // the div you rendered
      const form = document.getElementById('reply-form');

      // move the form under the post
      container.appendChild(form);
      form.classList.remove('d-none');
      document.getElementById('parent_id').value = parentId;
      document.getElementById('reply-content').focus();
    });
  });

  document.getElementById('cancel-reply').onclick = () => {
    document.getElementById('reply-form').classList.add('d-none');
  };
</script>


<?php include __DIR__ . '/../includes/footer.php'; ?>