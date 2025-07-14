<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// session_start(); should we start session here since it's in index.php and other php files? 
$userProfilePic = '/images/default.png';
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

  <link rel="stylesheet" href="/anarchosapien/styles/style.css" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Pirata+One&family=Special+Elite&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="icon" type="image/x-icon" href="/anarchosapien/icons/molotov_1_.ico" />
  <script src="https://cdn.tiny.cloud/1/9081gw3enl4pnnjnkoat9hahsqxz8gm6ot9gd46m3zlamg02/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<body>
  <!-- ── NAVBAR BEGIN ───────────────── -->
  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand" href="/anarchosapien/index.php">
        <img src="/anarchosapien/images/anarchosapien-logo.png"
          alt="AnarchoSapien logo"
          class="as-logo" />
      </a>
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
            <a class="nav-link" href="/anarchosapien/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/anarchosapien/about/about.php">About</a>
          </li>
          <li>
            <a class="nav-link" href="#">Articles</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" aria-current="page" href="/anarchosapien/forums/index.php">Forums</a>
          </li>
          <!-- TODO: Only show for author/admin users once user types are added to db -->
          <?php
          if (isset($_SESSION['user_id'])):
            echo "<li class=\"nav-item\">";
            echo "<a class=\"nav-link\" aria-current=\"page\" href=\"/anarchosapien/articles/create.php\">Create Article</a>";
            echo "</li>";
          endif; ?>
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
              <?php
              $tagStmt = $pdo->query("
                  SELECT t.name, COUNT(at.article_id) AS usage_count
                  FROM tags t
                  LEFT JOIN article_tags at ON t.id = at.tag_id
                  GROUP BY t.id
                  ORDER BY usage_count DESC, t.name ASC
                  LIMIT 5
                ");
              while ($tag = $tagStmt->fetch()):
              ?>
                <li>
                  <a class="dropdown-item" href="/anarchosapien/tags/tag.php?name=<?= urlencode($tag['name']) ?>">
                    <?= htmlspecialchars(ucfirst($tag['name'])) ?>
                  </a>
                </li>
              <?php endwhile; ?>
              <li>
                <hr class="dropdown-divider" />
              </li>
              <li><a class="dropdown-item" href="/anarchosapien/tags/all_tags.php">All Tags</a></li>
            </ul>

          </li>
        </ul>

        <div class="d-flex">
          <?php if (isset($_SESSION['user_name'])): ?>
            <a href="/anarchosapien/users/profile.php?id=<?= $_SESSION['user_id'] ?>">
              <img src="/anarchosapien/users/<?= htmlspecialchars($userProfilePic) ?>" class="rounded-circle pfp-thumbnail" alt="Profile" style="margin-right:8px; width: 35px; height: 35px; object-fit: cover;">
            </a>
            <!-- ── dropdown menu for user/profile/edit profile/logout ───────────────── -->
            <div class="dropdown me-3">
              <a
                class="nav-link dropdown-toggle my-2"
                href="#"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false">
                <span class="navbar-text welcome-user">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!</span>
              </a>
              <ul class="dropdown-menu bg-dark text-end">
                <li><a class="dropdown-item text-light" href="/anarchosapien/users/profile.php?id=<?= $_SESSION['user_id'] ?>">My Profile</a></li>
                <li><a class="dropdown-item text-light" href="/anarchosapien/users/edit_profile.php">Edit Profile</a></li>
                <li><a class="dropdown-item text-light" href="/anarchosapien/users/logout.php">Logout</a></li>
              </ul>

              <!-- ── user is logged out or not a member - show login/register btns ───────────────── -->
            <?php else: ?>
              <a href="/anarchosapien/users/login.php" class="btn btn-outline-success me-2">Login</a>
              <a href="/anarchosapien/users/register.php" class="btn btn-outline-primary">Register</a>
            <?php endif; ?>
            </div>
        </div>
      </div>
  </nav>
  <!-- ── NAVBAR END ───────────────── -->
  </nav>

  <!-- ── JUMBOTRON BEGIN ───────────────── -->
  <div class="jumbotron cover">
    <div class="container">
      <h1 class="display-3">AnarchoSapien</h1>
      <p class="lead">No Gods, No Masters; Only Knowledge & Freedom</p>
    </div>
  </div>
  <!-- JUMBOTRON END -->