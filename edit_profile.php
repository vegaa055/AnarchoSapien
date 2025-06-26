<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) die("Unauthorized");

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) die("User not found");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $bio = trim($_POST['bio']);
  $profile_picture = $user['profile_picture'];

  if (!empty($_POST['cropped_image'])) {
    $data = $_POST['cropped_image'];
    if (preg_match('/^data:image\/png;base64,/', $data)) {
      $data = substr($data, strpos($data, ',') + 1);
      $data = base64_decode($data);

      $uploadDir = "uploads/users/";
      if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);

      $filename = 'user_' . $user_id . '_' . uniqid() . '.png';
      $path = $uploadDir . $filename;

      if (file_put_contents($path, $data)) {
        if ($profile_picture && file_exists($profile_picture) && $profile_picture !== 'images/default.png') {
          unlink($profile_picture);
        }
        $profile_picture = $path;
      }
    }
  }

  $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_picture = ? WHERE id = ?");
  $stmt->execute([$bio, $profile_picture, $user_id]);

  header("Location: profile.php?id=$user_id");
  exit;
}

include 'header.php';
?>

<!-- Cropper.js CSS -->
<link href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.css" rel="stylesheet" />

<div class="container mt-5" style="max-width: 600px;">
  <h3>Edit Profile</h3>
  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Profile Picture</label><br>
      <img id="currentPreview" src="<?= $user['profile_picture'] ?: 'images/default.png' ?>" class="rounded mb-2" style="width: 100px; height: 100px; object-fit: cover;">
      <input type="file" name="profile_picture" id="profile_picture_input" class="form-control" accept="image/*">
      <input type="hidden" name="cropped_image" id="cropped_image">
      <canvas id="crop_canvas" style="display: none;"></canvas>
    </div>
    <div class="mb-3">
      <label class="form-label">About Me</label>
      <textarea name="bio" rows="5" class="form-control"><?= htmlspecialchars($user['bio']) ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
  </form>


</div>
<!-- Modal for cropping -->
<div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="cropModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cropModalLabel">Crop Profile Picture</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <img id="cropImage" style="max-width: 100%; display: block; margin: auto;">
      </div>
      <div class="modal-footer">
        <button type="button" id="cropConfirm" class="btn btn-primary" data-bs-dismiss="modal">Crop & Save</button>
      </div>
    </div>
  </div>
</div>
<!-- Cropper.js JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.13/dist/cropper.min.js"></script>
<script>
  let cropper;
  const input = document.getElementById('profile_picture_input');
  const cropImage = document.getElementById('cropImage');
  const croppedInput = document.getElementById('cropped_image');
  const cropModal = new bootstrap.Modal(document.getElementById('cropModal'));

  input.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
      cropImage.src = reader.result;
      cropModal.show();

      cropModal._element.addEventListener('shown.bs.modal', () => {
        cropper = new Cropper(cropImage, {
          aspectRatio: 1,
          viewMode: 1
        });
      }, {
        once: true
      });
    };
    reader.readAsDataURL(file);
  });

  document.getElementById('cropConfirm').addEventListener('click', () => {
    if (cropper) {
      const canvas = cropper.getCroppedCanvas({
        width: 300,
        height: 300
      });
      croppedInput.value = canvas.toDataURL('image/png');
      document.getElementById('currentPreview').src = canvas.toDataURL('image/png');
      cropper.destroy();
    }
  });
</script>

<?php include 'footer.php'; ?>