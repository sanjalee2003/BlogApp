<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

if (!isset($_GET['blog_id']) || empty($_GET['blog_id'])) {
    die("Blog not found.");
}

$blog_id = $_GET['blog_id'];
$user_id = $_SESSION['user_id'];

/* get blog to verify ownership */
$sql = "SELECT * FROM blogPost WHERE blog_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $blog_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Blog not found.");
}

$blog = $result->fetch_assoc();

/* allow only owner to delete */
if ($blog['user_id'] != $user_id) {
    die("You are not allowed to delete this blog.");
}

/* delete image if exists */
if (!empty($blog['image'])) {
    $imagePath = "../uploads/" . $blog['image'];
    
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

/* delete blog from database */
$deleteSql = "DELETE FROM blogPost WHERE blog_id = ? AND user_id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("ii", $blog_id, $user_id);

if ($deleteStmt->execute()) {
    header("Location: ../index.php");
    exit();
} else {
    echo "Error deleting blog.";
}
?>