<?php
require_once 'db.php';
// session_start();
$userProfilePic = 'images/default.png';
if (isset($_SESSION['user_id'])) {
  $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE id = ?");
  $stmt->execute([$_SESSION['user_id']]);
  $result = $stmt->fetch();
  if ($result && $result['profile_picture']) {
    $userProfilePic = $result['profile_picture'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>AnarchoSapien</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Special+Elite&display=swap"
    rel="stylesheet" />
  <script src="https://cdn.tiny.cloud/1/9081gw3enl4pnnjnkoat9hahsqxz8gm6ot9gd46m3zlamg02/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>
  <!-- NAVBAR BEGIN -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="index.php">
        <img
          src="images/anarchosapien-logo.png"
          alt="AnarchoSapien logo - Anarchy A, owl in front of an open book."
          class="as-logo" /></a>
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Forum</a>
          </li>
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false">
              Topics
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Philosophy</a></li>
              <li><a class="dropdown-item" href="#">Technology</a></li>
              <li><a class="dropdown-item" href="#">History</a></li>
              <li><a class="dropdown-item" href="#">Paranormal</a></li>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li><a class="dropdown-item" href="#">All Articles</a></li>
            </ul>
          </li>

        </ul>
        <div class="d-flex">
          <?php if (isset($_SESSION['user_name'])): ?>
            <a href="profile.php?id=<?= $_SESSION['user_id'] ?>">
              <img src="<?= htmlspecialchars($userProfilePic) ?>" class="rounded-circle pfp-thumbnail" alt="Profile" style="margin-right:8px; width: 35px; height: 35px; object-fit: cover;">
            </a>
            <span class="navbar-text me-3 welcome-user">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
            <a href="logout.php" class="btn btn-outline-danger">Logout</a>
          <?php else: ?>
            <a href="login.php" class="btn btn-outline-success me-2">Login</a>
            <a href="register.php" class="btn btn-outline-primary">Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <!-- NAVBAR END -->
  </nav>
  <!-- JUMBOTRON BEGIN -->
  <div class="jumbotron cover">
    <div class="container">
      <h1 class="display-3">AnarchoSapien</h1>
      <p class="lead">No Gods, No Masters; Only Freedom & Knowledge</p>
    </div>
  </div>
  <!-- JUMBOTRON END -->