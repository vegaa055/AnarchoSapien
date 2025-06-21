<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$userId = $_SESSION['user_id'];
$isDraft = isset($_GET['draft']) && $_GET['draft'] == 1;

if ($isDraft) {
  $uploadDir = "uploads/draft_user_$userId/";
} elseif (isset($_GET['article_id']) && is_numeric($_GET['article_id'])) {
  $articleId = intval($_GET['article_id']);
  $uploadDir = "uploads/article_$articleId/";
} else {
  http_response_code(400);
  echo json_encode(['error' => 'Missing article ID or draft flag.']);
  exit;
}

if (!file_exists($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $tmpPath = $_FILES['file']['tmp_name'];
  $filename = basename($_FILES['file']['name']);
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $safeName = uniqid('img_') . '.' . strtolower($ext);
  $targetPath = $uploadDir . $safeName;

  if (move_uploaded_file($tmpPath, $targetPath)) {
    echo json_encode(['location' => $targetPath]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file.']);
  }
} else {
  http_response_code(400);
  echo json_encode(['error' => 'Upload failed.']);
}
