<?php
require_once 'db.php';
header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query === '') {
    echo json_encode([]);
    exit;
}

$words = preg_split('/\s+/', strtolower($query));
if (empty($words)) {
    echo json_encode([]);
    exit;
}
$result = $conn->query("
    SELECT b.id, b.title, b.image, a.name AS author_name
    FROM books b
    JOIN authors a ON b.author_id = a.id
");

$invertedIndex = [];
$bookData = [];

while ($row = $result->fetch_assoc()) {
    $bookId = $row['id'];
    $title = strtolower($row['title']);
    $bookData[$bookId] = $row;

    $tokens = preg_split('/\s+/', $title);
    foreach ($tokens as $token) {
        $invertedIndex[$token][] = $bookId;
    }
}

$matchingIds = [];

foreach ($words as $i => $word) {

    $matchedKeys = array_filter(array_keys($invertedIndex), function ($key) use ($word) {
        return strpos($key, $word) === 0;
    });

    if (empty($matchedKeys)) {
        $matchingIds = [];
        break;
    }

    $ids = [];
    foreach ($matchedKeys as $key) {
        $ids = array_merge($ids, $invertedIndex[$key]);
    }

    $ids = array_unique($ids);

    if ($i === 0) {
        $matchingIds = $ids;
    } else {
        $matchingIds = array_intersect($matchingIds, $ids);
    }

    if (empty($matchingIds)) break;
}

if (empty($matchingIds)) {
    echo json_encode([]);
    exit;
}

$placeholders = implode(',', array_fill(0, count($matchingIds), '?'));
$types = str_repeat('i', count($matchingIds));

$stmt = $conn->prepare("
    SELECT b.id, COUNT(cv.id) AS total_views
    FROM books b
    JOIN chapters c ON b.id = c.book_id
    LEFT JOIN chapter_views cv ON c.id = cv.chapter_id
    WHERE b.id IN ($placeholders)
    GROUP BY b.id
");

$stmt->bind_param($types, ...$matchingIds);
$stmt->execute();
$viewResults = $stmt->get_result();

$viewsMap = [];
while ($row = $viewResults->fetch_assoc()) {
    $viewsMap[$row['id']] = (int)$row['total_views'];
}

$final = [];
foreach ($matchingIds as $bookId) {
    $book = $bookData[$bookId];
    $book['total_views'] = $viewsMap[$bookId] ?? 0;
    $book['id'] = (int)$bookId;
    $final[] = $book;
}

usort($final, function ($a, $b) {
    return $b['total_views'] <=> $a['total_views'];
});

echo json_encode(array_slice($final, 0, 10));
exit;