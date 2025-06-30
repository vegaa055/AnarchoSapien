<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) die("Missing ID");
$comment_id = (int) $_GET['id'];

// Fetch comment
$stmt = $pdo->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) die("Comment not found");

// Check if user is owner or admin
if (
  !isset($_SESSION['user_id']) ||
  ($_SESSION['user_id'] != $comment['user_id'] && !($_SESSION['user_is_admin'] ?? false))
) {
  die("Unauthorized");
}

// Delete comment
$stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);

header("Location: /anarchosapien/articles/view.php?id=" . $comment['article_id']);
exit;
