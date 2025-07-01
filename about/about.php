<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
session_start(); // Start the session to access session variables

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<div class="container text-center">
  <h1 class="mt-5 about-title">About Us</h1>
  <div class="about-content">
    <p>
      AnarchoSapien represents freedom and the pursuit of knowledge.
    </p>
    <p>
      Through knowledge we have lifted ourselves to become the dominant lifeforms on the planet.
    </p>
    <p>
      Through freedom we forge new paths and strengthen ourselves through collaborative efforts that benefit all of humankind.
    </p>
  </div>
  <img src="/anarchosapien/images/new-day.png" alt="">
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>