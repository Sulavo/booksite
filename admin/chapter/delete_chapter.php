<?php
include '../../includes/db.php';
include '../../auth_checks/admin_auth.php';

if (!isset($_GET['chapter_id'], $_GET['book_id'])) {
    die("Missing chapter or book ID.");
}

$chapter_id = intval($_GET['chapter_id']);
$book_id = intval($_GET['book_id']);

$stmt = $conn->prepare("DELETE FROM chapters WHERE id = ?");
$stmt->bind_param("i", $chapter_id);

if ($stmt->execute()) {
    header("Location: view_chapters.php?book_id=$book_id&status=chapter_deleted");
    exit();
} else {
    echo "Error deleting chapter.";
}