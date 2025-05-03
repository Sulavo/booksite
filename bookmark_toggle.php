<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    ob_end_clean();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    ob_end_clean();
    exit;
}

$bookId = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
$current = isset($_POST['current']) ? intval($_POST['current']) : 0;

if (!$bookId) {
    echo json_encode(['success' => false, 'error' => 'Missing book ID']);
    ob_end_clean();
    exit;
}

$userId = getUserId();

if ($current) {
    // Already bookmarked -> REMOVE it
    $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND book_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'DB Error: ' . $conn->error]);
        ob_end_clean();
        exit;
    }
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    $stmt->close();
    $newStatus = false;
} else {
    // Not bookmarked -> ADD it
    $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, book_id) VALUES (?, ?)");
    if (!$stmt) {
        echo json_encode(['success' => false, 'error' => 'DB Error: ' . $conn->error]);
        ob_end_clean();
        exit;
    }
    $stmt->bind_param("ii", $userId, $bookId);
    $stmt->execute();
    $stmt->close();
    $newStatus = true;
}

// Success
echo json_encode(['success' => true, 'bookmarked' => $newStatus]);
//ob_end_clean();
//exit;
?>