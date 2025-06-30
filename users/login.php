<?php

require_once __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
session_start();

$errors = [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $user_name = trim($_POST['user_name']); // Get the username from the form
  $password = $_POST['password'];   // Get the password from the form

  if (empty($user_name) || empty($password)) {
    $errors[] = "Both fields are required.";
  } else {
    $stmt = $pdo->prepare("SELECT id, user_name, password_hash FROM users WHERE user_name = ?");  // Prepare the SQL statement to prevent SQL injection
    // Execute the statement with the provided username
    $stmt->execute([$user_name]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['user_name'] = $user['user_name'];
      header("Location: /anarchosapien/index.php");
      exit;
    } else {
      $errors[] = "Invalid username or password.";
    }
  }
}
?>

<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - AnarchoSapien</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-dark text-white">
  <div class="container mt-5">
    <h2>Login to AnarchoSapien</h2>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-3">
        <label for="user_name" class="form-label">Username</label>
        <input type="text" class="form-control" id="user_name" name="user_name" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-outline-success">Login</button>
    </form>
  </div>

  <!-- footer -->
  <?php include __DIR__ . '/../includes/footer.php'; ?>



</body>

</html>