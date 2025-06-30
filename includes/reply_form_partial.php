<!-- expects $comment and $articleId in scope -->
<a href="#"
  class="small text-decoration-none"
  onclick="document.getElementById('reply-<?= $comment['id'] ?>').classList.toggle('d-none');return false;">
  Reply
</a>

<div id="reply-<?= $comment['id'] ?>" class="d-none mt-2">
  <form method="POST" action="/anarchosapien/comments/post_comment.php">
    <input type="hidden" name="article_id" value="<?= $articleId ?>">
    <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
    <?php if (!isset($_SESSION['user_id'])): ?>
      <input type="text" name="name" class="form-control mb-1" placeholder="Your name" required>
    <?php endif; ?>
    <textarea name="content" rows="2" class="form-control mb-1" placeholder="Reply…" required></textarea>
    <button type="submit" class="btn btn-sm btn-outline-primary">Reply</button>
  </form>
</div>