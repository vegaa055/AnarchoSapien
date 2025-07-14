<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['thread_id']) || !isset($_POST['vote'])) {
  http_response_code(400);
  exit('Invalid request.');
}

$thread_id = (int) $_POST['thread_id'];
$vote = (int) $_POST['vote'];
$user_id = $_SESSION['user_id'];

if (!in_array($vote, [1, -1])) {
  http_response_code(400);
  exit('Invalid vote value.');
}

// Insert or update vote
$stmt = $pdo->prepare("
  INSERT INTO thread_votes (thread_id, user_id, vote)
  VALUES (?, ?, ?)
  ON DUPLICATE KEY UPDATE vote = VALUES(vote)
");
$stmt->execute([$thread_id, $user_id, $vote]);

// Return new score
$stmt = $pdo->prepare("SELECT COALESCE(SUM(vote), 0) FROM thread_votes WHERE thread_id = ?");
$stmt->execute([$thread_id]);
echo $stmt->fetchColumn();
