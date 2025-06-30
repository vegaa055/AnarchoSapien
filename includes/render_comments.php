<?php
function renderComments(array $tree, array $byParent, int $level = 0, int $maxIndent = 5)
{
  global $pdo;
  if (!isset($tree)) return;
  foreach ($tree as $comment) {
    $indent = min($level, $maxIndent) * 2;
    $avatar = $comment['profile_picture'] ?: 'images/default.png';

    echo '<div class="ms-' . $indent . ' mb-3 mt-2">';  // indent wrapper
    echo '<div class="comment p-2 rounded">';           // single comment box

    echo '<div class="d-flex align-items-start mb-1">';
    echo '<a href="../users/profile.php?id=' . $comment['user_id'] . '">';
    echo '<img src="../users/' . htmlspecialchars($avatar) . '" class="rounded-circle me-2" style="width: 36px; height: 36px; object-fit: cover;">';
    echo '</a>';
    echo '<div>';
    echo '<strong>' . htmlspecialchars($comment['user_name'] ?? $comment['name']) . '</strong> ';
    echo '<small class="text-secondary">' .
      date('F j, Y H:i', strtotime($comment['commented_at'])) .
      '</small>';
    echo '<p>' . nl2br(htmlspecialchars($comment['content'])) . '</p>';

    // Show edit/delete buttons for comment owner or admin
    if (
      isset($_SESSION['user_id']) &&
      ($_SESSION['user_id'] == $comment['user_id'] || ($_SESSION['user_is_admin'] ?? false))
    ) {
      echo '<div class="mb-1">';
      echo '<a href="/anarchosapien/comments/edit_comment.php?id=' . $comment['id'] . '" class="btn btn-sm btn-outline-secondary me-1">Edit</a>';
      echo '<a href="/anarchosapien/comments/delete_comment.php?id=' . $comment['id'] . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Delete this comment?\');">Delete</a>';
      echo '</div>';
    }
    // Get like count
    $stmtLikes = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
    $stmtLikes->execute([$comment['id']]);
    $likeCount = $stmtLikes->fetchColumn();

    // Check if current user liked
    $userLiked = false;
    if (isset($_SESSION['user_id'])) {
      $stmtLiked = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
      $stmtLiked->execute([$comment['id'], $_SESSION['user_id']]);
      $userLiked = $stmtLiked->fetch() !== false;
    }

    // Output like UI
    echo '<div class="mt-1">';
    echo '<button class="like-comment btn btn-sm ' . ($userLiked ? 'btn-danger' : 'btn-outline-danger') . '" data-id="' . $comment['id'] . '">';
    echo '<i class="' . ($userLiked ? 'fa-solid' : 'fa-regular') . ' fa-thumbs-up"></i>';
    echo '</button> ';
    echo '<span id="like-count-' . $comment['id'] . '">' . $likeCount . ' like' . ($likeCount != 1 ? 's' : '') . '</span>';
    echo '</div>';

    // Include reply form
    $articleId = htmlspecialchars($comment['article_id']);
    include 'reply_form_partial.php';

    echo '</div></div>'; // close content/avatar wrapper
    echo '</div>';       // close .comment box

    // Recurse into children
    if (isset($byParent[$comment['id']])) {
      renderComments($byParent[$comment['id']], $byParent, $level + 1, $maxIndent);
    }

    echo '</div>';       // close indent wrapper
  }
}
