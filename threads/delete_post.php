<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();

$post_id = $_GET['id'] ?? null;
if (!$post_id || !is_numeric($post_id)) {
  die("Invalid post ID.");
}

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post || $_SESSION['user_id'] != $post['user_id']) {
  die("Unauthorized.");
}

// Optionally prevent deleting the first post (thread starter)
$stmt = $pdo->prepare("SELECT id FROM posts WHERE thread_id = ? ORDER BY created_at ASC LIMIT 1");
$stmt->execute([$post['thread_id']]);
$first_post = $stmt->fetchColumn();

if ($post['id'] == $first_post) {
  die("You cannot delete the original thread post.");
}

$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$post_id]);

header("Location: view.php?thread_id=" . $post['thread_id']);
exit;
