<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("config/db.php");

$search = "";

if (isset($_GET['search'])) {
    $search = trim($_GET['my_search']);

    $sql = "SELECT blogPost.*, User.username
            FROM blogPost
            JOIN User ON blogPost.user_id = User.user_id
            WHERE blogPost.title LIKE ? 
               OR blogPost.content LIKE ? 
               OR User.username LIKE ?
            ORDER BY blogPost.created_at DESC";

    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

} else {
    $sql = "SELECT blogPost.*, User.username
            FROM blogPost
            JOIN User ON blogPost.user_id = User.user_id
            ORDER BY blogPost.created_at DESC";

    $result = $conn->query($sql);
}

/* get single blog for popup if blog_id is set */
$selected_blog = null;

if (isset($_GET['blog_id']) && !empty($_GET['blog_id'])) {
    $blog_id = (int) $_GET['blog_id'];

    $view_sql = "SELECT blogPost.*, User.username
                 FROM blogPost
                 JOIN User ON blogPost.user_id = User.user_id
                 WHERE blogPost.blog_id = ?";

    $stmt = $conn->prepare($view_sql);
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $view_result = $stmt->get_result();

    if ($view_result->num_rows > 0) {
        $selected_blog = $view_result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Blog App</title>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="navbar">
  <h1>Scriblio</h1>

  <div class="nav-links">
    <?php if (isset($_SESSION["user_id"])): ?>
      <span class="nav-greeting">Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
      <a href="index.php">Home</a>
      <a href="blog/create.php">Create Blog</a>
      <a href="auth/logout.php">Logout</a>
    <?php else: ?>
      <a href="auth/login.php">Login</a>
      <a href="auth/register.php">Register</a>
    <?php endif; ?>
  </div>
</div>

<div class="container">

  <?php if (isset($_SESSION["user_id"])): ?>
    <div class="welcome-box">
      <p>Search blogs by title, content, or author name.</p>

      <form action="" method="GET" class="search-form-modern">
        <div class="search-input-group">
          <span class="search-icon">🔍</span>
          <input
            type="text"
            name="my_search"
            class="search-input-modern"
            placeholder="Search blogs, content, or author..."
            value="<?php echo htmlspecialchars($search); ?>"
          >
        </div>

        <div class="search-actions">
          <button type="submit" name="search" class="search-btn-modern">Search</button>

          <?php if (!empty($search)): ?>
            <a href="index.php" class="clear-search-btn-modern">Clear</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="hero">
      <h2>Welcome to the Blog Application</h2>
      <p>Create an account, log in, and start sharing your blogs.</p>

      <div class="action-buttons">
        <a href="auth/register.php" class="btn-getstarted">Get Started</a>
      </div>
    </div>
  <?php endif; ?>

  <h2 class="section-title">Latest Blogs</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="blog-grid">

      <?php while($row = $result->fetch_assoc()): ?>
        <div class="blog-card">

          <img 
            src="<?php echo (!empty($row["image"]) && file_exists("uploads/" . $row["image"])) 
                ? 'uploads/' . htmlspecialchars($row["image"]) 
                : 'uploads/default-postimage.jpg'; ?>" 
            alt="Blog Image" 
            class="blog-image"
          >

          <h3 class="blogtitle"><?php echo strtoupper(htmlspecialchars($row["title"])); ?></h3>

          <p class="meta">
            By <?php echo htmlspecialchars($row["username"]); ?> |
            <?php echo htmlspecialchars($row["created_at"]); ?>
          </p>

          <p class="blog-preview">
            <?php echo nl2br(htmlspecialchars(substr($row["content"], 0, 150))); ?>...
          </p>

          <div class="action-buttons card-actions">
            <a class="read-btn" href="index.php?blog_id=<?php echo $row["blog_id"]; ?>">
              Read More
            </a>
          </div>

        </div>
      <?php endwhile; ?>

    </div>
  <?php else: ?>
    <p style="margin-top:20px;">No matching blogs found.</p>
  <?php endif; ?>

</div>

<footer class="footer">
  <div class="footer-content">
    <h3>Scriblio</h3>
    <p>Share your thoughts, stories, and ideas with the world.</p>

    <div class="footer-links">
      <a href="index.php">Home</a>
      <a href="#">About</a>
      <a href="#">Contact</a>
    </div>

    <p class="copyright">
      © <?php echo date("Y"); ?> Scriblio. All rights reserved.
    </p>
  </div>
</footer>

<?php if ($selected_blog): ?>
  <div class="modal" id="blogModal">
    <div class="modal-content">

      <span class="close-btn" onclick="closePopup()">&times;</span>

      <h2 class="blogtitle"><?php echo strtoupper(htmlspecialchars($selected_blog["title"])); ?></h2>

      <p class="meta">
        By <?php echo htmlspecialchars($selected_blog['username']); ?> |
        Created: <?php echo htmlspecialchars($selected_blog['created_at']); ?>
        <?php if (!empty($selected_blog['updated_at'])): ?>
          | Updated: <?php echo htmlspecialchars($selected_blog['updated_at']); ?>
        <?php endif; ?>
      </p>

      <img
        src="<?php echo (!empty($selected_blog['image']) && file_exists('uploads/' . $selected_blog['image']))
          ? 'uploads/' . htmlspecialchars($selected_blog['image'])
          : 'uploads/default-postimage.jpg'; ?>"
        alt="Blog Image"
        class="view-image"
      >

      <div class="view-content">
        <?php echo nl2br(htmlspecialchars($selected_blog['content'])); ?>
      </div>

      <div class="action-buttons popup-actions">
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $selected_blog['user_id']): ?>
          <a href="blog/edit.php?blog_id=<?php echo $selected_blog['blog_id']; ?>" class="btn btn-secondary">Edit</a>

          <a
            href="blog/delete.php?blog_id=<?php echo $selected_blog['blog_id']; ?>"
            class="btn danger-btn"
            onclick="return confirm('Are you sure you want to delete this blog?')"
          >
            Delete
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
<?php endif; ?>

<script>
  function closePopup() {
    window.location.href = "index.php";
  }

  window.onclick = function(event) {
    const modal = document.getElementById("blogModal");
    if (modal && event.target === modal) {
      closePopup();
    }
  };
</script>

</body>
</html>