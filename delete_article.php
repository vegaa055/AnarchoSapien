<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  $stmt = $pdo->prepare("SELECT author_id FROM articles WHERE id = ?");
  $stmt->execute([$_GET['id']]);
  $article = $stmt->fetch();

  if (!$article) {
    die("Article not found.");
  }

  if ($article['author_id'] != $_SESSION['user_id']) {
    die("Unauthorized deletion attempt.");
  }

  $delete = $pdo->prepare("DELETE FROM articles WHERE id = ?");
  $delete->execute([$_GET['id']]);

  header("Location: index.php");
  exit;
} else {
  die("Invalid article ID.");
}
?>
