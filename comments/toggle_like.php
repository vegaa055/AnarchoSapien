<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['article_id'])) {
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$article_id = (int) $_POST['article_id'];
$user_id = $_SESSION['user_id'];

// Check if liked
$stmt = $pdo->prepare("SELECT 1 FROM article_likes WHERE article_id = ? AND user_id = ?");
$stmt->execute([$article_id, $user_id]);
$liked = $stmt->fetch();

if ($liked) {
  $stmt = $pdo->prepare("DELETE FROM article_likes WHERE article_id = ? AND user_id = ?");
  $stmt->execute([$article_id, $user_id]);
  $action = 'unliked';
} else {
  $stmt = $pdo->prepare("INSERT INTO article_likes (article_id, user_id) VALUES (?, ?)");
  $stmt->execute([$article_id, $user_id]);
  $action = 'liked';
}

// Get updated like count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM article_likes WHERE article_id = ?");
$stmt->execute([$article_id]);
$likeCount = $stmt->fetchColumn();

echo json_encode([
  'status' => $action,
  'likes' => $likeCount
]);
