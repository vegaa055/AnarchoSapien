<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include __DIR__ . '/../includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>../styles/style.css" />
<div class="container text-center">
  <h1 class="mt-5 section-title-2">About Us</h1>
  <hr class="text-danger" style="width: 50%; margin: auto; margin-top: 20px; margin-bottom: 20px;">
  <div class="about-content">
    <p>
      AnarchoSapien represents freedom and the pursuit of knowledge.
    </p>
    <p>
      Through knowledge we have lifted ourselves up to become the dominant lifeforms on the planet.
    </p>
    <p>
      Through freedom we forge new paths and strengthen ourselves through collaborative efforts that benefit all of humankind.
    </p>
  </div>
  <img src="/anarchosapien/images/new-day.png" class="img-fluid mt-4" alt="Image of anarchists celebrating a new day">
</div>
<div class="container mt-5 mission-section">
  <h2 class="section-title-2">Our Mission</h2>
  <p class="about-content">
    Our mission is to provide a platform for free expression, knowledge sharing, and community building.
    We believe in the power of ideas and the importance of open dialogue.
  </p>
  <h3 class="section-title-2">Join Us</h3>
  <p class="about-content">
    Whether you're an activist, a thinker, or just curious, we invite you to join our community.
    Together, we can explore new ideas and create a better future.
  </p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>