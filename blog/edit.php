<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    echo "Error: User session not found. Please login first.";
    exit();
}

if (!isset($_GET['blog_id']) || empty($_GET['blog_id'])) {
    die("Blog not found.");
}

$blog_id = (int) $_GET['blog_id'];
$user_id = $_SESSION['user_id'];
$error = "";
$title = "";
$content = "";

/* get existing blog */
$sql = "SELECT * FROM blogPost WHERE blog_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Blog not found.");
}

$blog = $result->fetch_assoc();

/* only owner can edit */
if ($blog['user_id'] != $user_id) {
    die("You are not allowed to edit this post.");
}

/* set default values */
$title = $blog['title'];
$content = $blog['content'];

/* update post */
if (isset($_POST['updatepost'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imageName = $blog['image'];

    if (empty($title) || empty($content)) {
        $error = "Title and content are required.";
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
            $fileType = $_FILES['image']['type'];

            if (in_array($fileType, $allowed)) {
                $imageName = time() . "_" . basename($_FILES['image']['name']);
                $target = "../uploads/" . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $error = "Image upload failed.";
                }
            } else {
                $error = "Invalid file type. Only PNG, JPG, JPEG, and GIF are allowed.";
            }
        }

        if (empty($error)) {
            $updateSql = "UPDATE blogPost 
                          SET title = ?, content = ?, image = ?, updated_at = NOW()
                          WHERE blog_id = ? AND user_id = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("sssii", $title, $content, $imageName, $blog_id, $user_id);

            if ($updateStmt->execute()) {
                header("Location: ../index.php?blog_id=" . $blog_id);
                exit();
            } else {
                $error = "Error updating post.";
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
  <title>Edit Blog | Scriblio</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<!-- NAVBAR -->
<div class="navbar">
  <h1>Scriblio</h1>

  <div class="nav-links">
    <span class="nav-greeting">Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
    <a href="../index.php">Home</a>
    <a href="create.php">Create Blog</a>
    <a href="../auth/logout.php">Logout</a>
  </div>
</div>

<div class="create-page-wrapper">
  <div class="create-blog-card">

    <div class="edit-blog-header">
      <h2>Edit Blog Post</h2>
      <p>Update your blogs and keep it polished.</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="message error">
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="create-blog-form">

      <div class="form-group">
        <label for="title">Blog Title <span class="required-star">*</span></label>
        <input
          type="text"
          id="title"
          name="title"
          class="form-control"
          placeholder="Enter your blog title"
          value="<?php echo htmlspecialchars($title); ?>"
          required
        >
      </div>

      <div class="form-group">
        <label for="content">Blog Content <span class="required-star">*</span></label>
        <textarea
          id="content"
          name="content"
          class="form-control form-textarea"
          placeholder="Write your blog content here..."
          required
        ><?php echo htmlspecialchars($content); ?></textarea>
      </div>

      <div class="form-group">
        <label class="current-image-label">Current Image</label>

        <div class="current-image-wrapper">
          <img
            src="<?php echo (!empty($blog['image']) && file_exists('../uploads/' . $blog['image']))
              ? '../uploads/' . htmlspecialchars($blog['image'])
              : '../uploads/default-postimage.jpg'; ?>"
            alt="Current Blog Image"
            class="current-blog-image"
          >
        </div>
      </div>

      <div class="form-group">
        <label for="image">Change Image</label>
        <input
          type="file"
          id="image"
          name="image"
          class="form-control file-input"
          accept="image/png, image/jpeg, image/jpg, image/gif"
        >
        <small class="form-hint">Optional. Upload a new image only if you want to replace the current one.</small>
      </div>

      <div class="form-group">
        <label>Author</label>
        <input
          type="text"
          class="form-control author-field"
          value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
          readonly
        >
      </div>

      <div class="create-form-actions">
        <a href="../index.php?blog_id=<?php echo $blog_id; ?>" class="btn-cancel-edit">Cancel</a>
        <button type="submit" name="updatepost" class="btn-update-post">Update Post</button>
      </div>

    </form>
  </div>
</div>

<footer class="footer">
  <div class="footer-content">
    <h3>Scriblio</h3>
    <p>Share your thoughts, stories, and ideas with the world.</p>

    <div class="footer-links">
      <a href="../index.php">Home</a>
      <a href="create.php">Create Blog</a>
      <a href="../auth/logout.php">Logout</a>
    </div>

    <p class="copyright">
      © <?php echo date("Y"); ?> Scriblio. All rights reserved.
    </p>
  </div>
</footer>

</body>
</html>