<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

if (
  $_SERVER['REQUEST_METHOD'] === 'POST'
  && isset($_POST['article_id'], $_POST['content'])
) {

  $article_id = (int) $_POST['article_id'];
  $content    = trim($_POST['content']);

  // parent_id = NULL for top-level, or the integer supplied
  $parent_id = isset($_POST['parent_id']) && is_numeric($_POST['parent_id']) ? (int) $_POST['parent_id'] : null;


  $user_id = $_SESSION['user_id'] ?? null;          // null = guest
  $name    = $user_id ? null : trim($_POST['name']); // guests supply a name

  if ($content !== '') {
    $sql = "INSERT INTO comments (article_id, user_id, name, content, parent_id)
                VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$article_id, $user_id, $name, $content, $parent_id]);
  }
}

header("Location: /anarchosapien/articles/view.php?id=" . $_POST['article_id']);
exit;
