<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/db.php");

$message = "";
$messageType = "";

$userName = "";
$email = "";

if (isset($_POST['register'])) {
    $userName = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (empty($userName) || empty($email) || empty($password) || empty($confirmPassword)) {
        $message = "All fields are required.";
        $messageType = "error";
    } elseif (strlen($userName) < 3) {
        $message = "Username must be at least 3 characters long.";
        $messageType = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $messageType = "error";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $messageType = "error";
    } elseif ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
        $messageType = "error";
    } else {
        $checkEmail = "SELECT * FROM User WHERE uemail = ?";
        $stmt = $conn->prepare($checkEmail);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email already exists.";
            $messageType = "error";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertQuery = "INSERT INTO User (username, uemail, upassword) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("sss", $userName, $email, $hashedPassword);

            if ($stmt->execute()) {
                $message = "Registration successful.";
                $messageType = "success";
                $userName = "";
                $email = "";
            } else {
                $message = "Error: " . $conn->error;
                $messageType = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-wrapper">
    <div class="auth-card">
      <h2>Create Account</h2>
      <p class="subtitle">Register to start using the blog application</p>

      <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="username">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            class="form-control"
            placeholder="Enter your username"
            value="<?php echo htmlspecialchars($userName); ?>"
          >
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            placeholder="Enter your email"
            value="<?php echo htmlspecialchars($email); ?>"
          >
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control"
            placeholder="Enter your password"
          >
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            class="form-control"
            placeholder="Confirm your password"
          >
        </div>

        <button type="submit" class="btn form-btn" name="register">Register</button>
      </form>

      <p class="bottom-text">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>

</body>
</html>