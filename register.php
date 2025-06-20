<?php
require_once 'db.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // Check if the form is submitted
    $user_name = trim($_POST['user_name']);   // Get the username from the form
    $email = trim($_POST['email']);           // Get the email from the form
    $password = $_POST['password'];           // Get the password from the form
    $confirm = $_POST['confirm_password'];    // Get the confirm password from the form

    if (empty($user_name) || empty($password) || empty($confirm) || empty($email)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE user_name = ? OR email = ?");
        $stmt->execute([$user_name, $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username or email already exists.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (user_name, password_hash, email) VALUES (?, ?, ?)");
            $insert->execute([$user_name, $hash, $email]);
            $success = true;
        }
    }
}
?>

<?php include('header.php'); ?>

  <div class="container mt-5">
    <h2>Register for AnarchoSapien</h2>

    <?php if ($success): ?>
      <div class="alert alert-success">Registration successful! You may now <a href="login.php">log in</a>.</div>
    <?php endif; ?>

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
      <!-- email -->
      <div class="mb-3">
        <label for="email" class="form-label">E-Mail</label>
        <input type="email" class="form-control" id="email" name="email" required>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
      </div>
      <div class="mb-3">
        <label for="confirm_password" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
      </div>
      <button type="submit" class="btn btn-outline-success mt-2">Register</button>
    </form>
  </div>
<?php include('footer.php'); ?>
