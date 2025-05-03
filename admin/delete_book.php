<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include '../auth_checks/admin_auth.php';

if (!isset($_GET['id'])) {
    die("❌ Book ID missing.");
}

$book_id = intval($_GET['id']);

// Begin transaction for safety
$conn->begin_transaction();

try {
    // 1️⃣ Delete all chapters for this book
    $delChapters = $conn->prepare("DELETE FROM chapters WHERE book_id = ?");
    $delChapters->bind_param("i", $book_id);
    $delChapters->execute();

    // 2️⃣ Delete all genre‐link rows for this book
    $delGenres = $conn->prepare("DELETE FROM book_genres WHERE book_id = ?");
    $delGenres->bind_param("i", $book_id);
    $delGenres->execute();

    // 3️⃣ Delete the book itself (cascades on any remaining FKs)
    $delBook = $conn->prepare("DELETE FROM books WHERE id = ?");
    $delBook->bind_param("i", $book_id);
    $delBook->execute();

    // Commit everything
    $conn->commit();

    // Redirect back to your book list page with a success message
    header("Location: book_list.php?status=book_deleted");
    exit; 
    
}catch (Exception $e) {
    // Roll back if anything failed
    $conn->rollback();
    echo "❌ Error deleting book: " . htmlspecialchars($e->getMessage());
}
?>