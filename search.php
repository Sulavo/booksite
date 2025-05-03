<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
include 'includes/navbar.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$limit = 30;
$offset = ($page - 1) * $limit;

function tokenize($text) {
    return preg_split('/[\s]+/', strtolower(trim($text)));
}

$book_map = [];
$inverted_index = [];
$book_views = [];

$result = $conn->query("
    SELECT b.id, b.title, b.image, b.status, a.name AS author_name,
           (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no,
           (SELECT COUNT(cv.id)
            FROM chapters c
            LEFT JOIN chapter_views cv ON c.id = cv.chapter_id
            WHERE c.book_id = b.id) AS total_views
    FROM books b
    JOIN authors a ON b.author_id = a.id
");

while ($row = $result->fetch_assoc()) {
    $book_id = $row['id'];
    $book_map[$book_id] = $row;
    $book_views[$book_id] = (int)$row['total_views'];

    foreach (tokenize($row['title']) as $word) {
        $inverted_index[$word][$book_id] = true;
    }
}

$matched_books = [];
if ($query !== '') {
    $query_words = tokenize($query);

    if (!empty($query_words)) {
        $first = array_shift($query_words);

        $prefix_matches = [];
        foreach ($inverted_index as $word => $ids) {
            if (str_starts_with($word, $first)) {
                foreach ($ids as $book_id => $_) {
                    $prefix_matches[$book_id] = true;
                }
            }
        }
        $matched_books = array_keys($prefix_matches);
        foreach ($query_words as $word) {
            if (!isset($inverted_index[$word])) {
                $matched_books = [];
                break;
            }
            $matched_books = array_values(array_intersect($matched_books, array_keys($inverted_index[$word])));
        }
    }
}

usort($matched_books, function ($a, $b) use ($book_views) {
    return $book_views[$b] <=> $book_views[$a];
});

$total_books = count($matched_books);
$total_pages = max(ceil($total_books / $limit), 1);
$paged_books = array_slice($matched_books, $offset, $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search - BookSite</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>
    <div class="layout-wrapper">
        <main id="content">
            <div class="section">
                <h2>Search Results for "<?= htmlspecialchars($query) ?>"</h2>
                <hr>

                <?php if ($query !== '' && !empty($paged_books)): ?>
                <div class="book-container">
                    <?php foreach ($paged_books as $book_id): 
                        $book = $book_map[$book_id]; ?>
                    <div class="book-card">
                        <a href="book.php?id=<?= $book['id'] ?>" class="book-link">
                            <div class="book-image-wrapper">
                                <img src="assets/images/books/<?= htmlspecialchars($book['image'] ?: 'default.png') ?>"
                                    alt="<?= htmlspecialchars($book['title']) ?>">
                                <div class="status-badge"><?= htmlspecialchars($book['status']) ?></div>
                                <div class="view-more">View More</div>
                            </div>
                            <div class="book-info">
                                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                                <div class="book-meta">
                                    <div><span>Author:</span> <?= htmlspecialchars($book['author_name']) ?></div>
                                    <div><span>Latest Chapter:</span> <?= $book['latest_chapter_no'] ?? 'N/A' ?></div>
                                    <div><span>Total Views:</span> <?= number_format($book_views[$book_id]) ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination"
                    style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 30px; flex-wrap: wrap;">
                    <?php if ($page > 1): ?>
                    <a href="?q=<?= urlencode($query) ?>&page=<?= $page - 1 ?>" class="purple-btn">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?q=<?= urlencode($query) ?>&page=<?= $i ?>"
                        class="purple-btn <?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?q=<?= urlencode($query) ?>&page=<?= $page + 1 ?>" class="purple-btn">Next</a>
                    <?php endif; ?>
                </div>

                <?php else: ?>
                <p>No Book Found.</p>
                <?php endif; ?>
            </div>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</body>

</html>