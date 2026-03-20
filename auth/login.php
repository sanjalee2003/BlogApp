<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/db.php");

$message = "";
$messageType = "";

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password = md5($password);

    $sql = "SELECT * FROM User WHERE uemail = '$email' AND upassword = '$password'";
    $result = $conn->query($sql);

    if($result->num_rows > 0){
        $user = $result->fetch_assoc();

        $_SESSION["user_id"] = $user["user_id"];
        $_SESSION["username"] = $user["username"];

        header("Location: ../index.php");
        exit();
    } else {
        $message = "Invalid email or password.";
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

  <div class="auth-wrapper">
    <div class="auth-card">
      <h2>Login</h2>
      <p class="subtitle">Sign in to access your account</p>

      <?php if (!empty($message)): ?>
        <div class="message <?php echo $messageType; ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            class="form-control"
            placeholder="Enter your email"
            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
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

        <button type="submit" class="btn form-btn" name="login">Login</button>
      </form>

      <p class="bottom-text">
        Don’t have an account? <a href="register.php">Register here</a>
      </p>
    </div>
  </div>

</body>
</html>