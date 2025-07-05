<div class="mb-3">
  <label for="tags" class="form-label">Tags</label>
  <select name="tags[]" id="tags" class="form-select" multiple>
    <?php
    $tagStmt = $pdo->query("SELECT id, name FROM tags ORDER BY name");
    while ($tag = $tagStmt->fetch()):
    ?>
      <option value="<?= $tag['id'] ?>"
        <?php if (isset($selectedTags) && in_array($tag['id'], $selectedTags)) echo 'selected'; ?>>
        <?= htmlspecialchars($tag['name']) ?>
      </option>
    <?php endwhile; ?>
  </select>
  <small class="form-text text-muted">Hold Ctrl (Cmd on Mac) to select multiple tags.</small>
</div>