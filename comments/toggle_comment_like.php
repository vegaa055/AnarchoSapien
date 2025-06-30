<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_POST['comment_id'], $_SESSION['user_id'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$comment_id = (int) $_POST['comment_id'];
$user_id = $_SESSION['user_id'];

// Toggle like
$stmt = $pdo->prepare("SELECT 1 FROM comment_likes WHERE comment_id = ? AND user_id = ?");
$stmt->execute([$comment_id, $user_id]);
$liked = $stmt->fetch();

if ($liked) {
  $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE comment_id = ? AND user_id = ?");
  $stmt->execute([$comment_id, $user_id]);
  $action = 'unliked';
} else {
  $stmt = $pdo->prepare("INSERT INTO comment_likes (comment_id, user_id) VALUES (?, ?)");
  $stmt->execute([$comment_id, $user_id]);
  $action = 'liked';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
$stmt->execute([$comment_id]);
$count = $stmt->fetchColumn();

echo json_encode(['status' => $action, 'likes' => $count]);
