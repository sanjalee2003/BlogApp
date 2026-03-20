<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");


include("../config/db.php");

$message = "";
$messageType = "";

if (isset($_POST['register'])){
    $userName = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = md5($password);

    $checkEmail = "SELECT * FROM User Where uemail = '$email'";
    $result = $conn->query($checkEmail);
    if($result->num_rows>0){
        $message = "Email already exists.";
        $messageType = "error";
    }

    else{
        $insertQuery = "INSERT INTO User(username,uemail,upassword) VALUES ('$userName','$email','$password')";
        if($conn->query($insertQuery) === TRUE){
            $message = "Regstration successfull";
            $messageType = "success";
        }

        else{
            $message = "Error: " . $conn->error;
            $messageType = "error";
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
            value="<?php echo isset($userName) ? htmlspecialchars($userName) : ''; ?>"
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

        <button type="submit" class="btn form-btn" name = "register">Register</button>
      </form>

      <p class="bottom-text">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>

</body>
</html>