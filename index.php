<?php
session_start();
include('header.php');
?>


<div class="main-content">
  <div class="featured-article">
    <h2>WTF</h2>
    <h4>Welcome to the AnarchoSapien</h4>
    <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Necessitatibus ex eaque rem, corporis quo ut.</p>
    <a href="#" class="btn btn-outline-success">Read More</a>
  </div>

  <div class="article-list">
    <h3>Recent Posts</h3>
    <ul>
      <?php
      $articles = [
        "Reclaiming Knowledge in a Controlled World",
        "Hack the System: Coding for the Free Mind",
        "Desert Legends and the Ghosts of Cochise",
        "Star Maps and State Maps: Who Draws the Lines?",
        "The Sound of Rebellion: Mixing Beats for Resistance"
      ];
      foreach ($articles as $title) {
        echo "<li><a href=\"#\">$title</a></li>";
      }
      ?>
    </ul>
  </div>
</div>

<?php include('footer.php'); ?>