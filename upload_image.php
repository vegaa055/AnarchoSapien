<?php
// upload_image.php
$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $tmpPath = $_FILES['file']['tmp_name'];
  $name = uniqid() . '-' . basename($_FILES['file']['name']);
  $targetPath = $uploadDir . $name;

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
