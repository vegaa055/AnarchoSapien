<?php
// Assumes $pdo, $articleId, and $_POST['tags'] (array of tag IDs) are available
if (!empty($_POST['tags']) && is_array($_POST['tags'])) {
    // Remove any existing tags (in case of update)
    $pdo->prepare("DELETE FROM article_tags WHERE article_id = ?")->execute([$articleId]);

    // Insert new tag associations
    $insertStmt = $pdo->prepare("INSERT INTO article_tags (article_id, tag_id) VALUES (?, ?)");
    foreach ($_POST['tags'] as $tagId) {
        if (is_numeric($tagId)) {
            $insertStmt->execute([$articleId, (int)$tagId]);
        }
    }
}
