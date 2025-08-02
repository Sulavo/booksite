<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include '../auth_checks/admin_auth.php';

if (!isset($_GET['id'])) {
    die("❌ Book ID missing.");
}

$book_id = intval($_GET['id']);


$conn->begin_transaction();

try {

    $delChapters = $conn->prepare("DELETE FROM chapters WHERE book_id = ?");
    $delChapters->bind_param("i", $book_id);
    $delChapters->execute();

    $delGenres = $conn->prepare("DELETE FROM book_genres WHERE book_id = ?");
    $delGenres->bind_param("i", $book_id);
    $delGenres->execute();

    $delBook = $conn->prepare("DELETE FROM books WHERE id = ?");
    $delBook->bind_param("i", $book_id);
    $delBook->execute();

  
    $conn->commit();

 
    header("Location: book_list.php?status=book_deleted");
    exit; 
    
}catch (Exception $e) {

    $conn->rollback();
    echo "❌ Error deleting book: " . htmlspecialchars($e->getMessage());
}
?>