<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id'], $_POST['post_id'], $_POST['vote'])) {
  http_response_code(400);
  exit;
}
$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];
$vote = (int)$_POST['vote'];
if (!in_array($vote, [1, -1])) {
  http_response_code(400);
  exit;
}

// Insert or update vote
$stmt = $pdo->prepare("
  INSERT INTO post_votes (post_id, user_id, vote)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE vote = VALUES(vote)
");


$stmt->execute([$post_id, $user_id, $vote]);

// Get updated score
$stmt2 = $pdo->prepare("SELECT COALESCE(SUM(vote),0) FROM post_votes WHERE post_id = ?");
$stmt2->execute([$post_id]);
echo $stmt2->fetchColumn();
