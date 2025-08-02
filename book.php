<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
include 'includes/navbar.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Book ID not specified.");
}

$book_id = (int)$_GET['id'];

$book_stmt = $conn->prepare("
    SELECT b.*, a.name AS author_name 
    FROM books b 
    JOIN authors a ON b.author_id = a.id 
    WHERE b.id = ?
");
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book_result = $book_stmt->get_result();
$book = $book_result->fetch_assoc();
$book_stmt->close();

if (!$book) {
    die("Book not found.");
}

$genres = [];
$genres_stmt = $conn->prepare("
    SELECT g.name 
    FROM genres g 
    JOIN book_genres bg ON g.id = bg.genre_id 
    WHERE bg.book_id = ?
");
$genres_stmt->bind_param("i", $book_id);
$genres_stmt->execute();
$genres_result = $genres_stmt->get_result();
while ($row = $genres_result->fetch_assoc()) {
    $genres[] = $row['name'];
}
$genres_stmt->close();

$chapters = [];
$chapters_stmt = $conn->prepare("
    SELECT id, chapter_no, chapter_name, updated_at 
    FROM chapters 
    WHERE book_id = ? 
    ORDER BY chapter_no DESC
");
$chapters_stmt->bind_param("i", $book_id);
$chapters_stmt->execute();
$chapters_result = $chapters_stmt->get_result();
while ($row = $chapters_result->fetch_assoc()) {
    $chapters[] = $row;
}
$chapters_stmt->close();

$isBookmarked = false;
if (isLoggedIn()) {
    $userId = getUserId();
    $bookmark_check = $conn->prepare("
        SELECT id 
        FROM bookmarks 
        WHERE user_id = ? AND book_id = ?
    ");
    $bookmark_check->bind_param("ii", $userId, $book_id);
    $bookmark_check->execute();
    $bookmark_check->store_result();
    $isBookmarked = $bookmark_check->num_rows > 0;
    $bookmark_check->close();
}


$first_chapter = $chapters ? end($chapters) : null;
$latest_chapter = $chapters ? reset($chapters) : null;

function limit_words($text, $limit = 100) {
    $words = preg_split('/\s+/', strip_tags($text));
    return htmlspecialchars(count($words) <= $limit 
        ? implode(' ', $words) 
        : implode(' ', array_slice($words, 0, $limit)) . '...');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($book['title']) ?></title>
    <link rel="stylesheet" href="assets/css/books.css">
</head>

<body>
    <div class="layout-wrapper">
        <main id="content">
            <div class="book-container">
                <div class="book-top">
                    <img src="assets/images/books/<?= htmlspecialchars($book['image']) ?>" alt="Book Cover"
                        class="book-cover">

                    <div class="book-info">
                        <h1 class="book-title"><?= htmlspecialchars($book['title']) ?></h1>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <button id="bookmarkBtn" data-bookmarked="<?= $isBookmarked ? '1' : '0' ?>"
                                class="bookmark-btn">
                                <?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?>
                            </button>
                            <div id="bookmark-status" style="display:inline-block;"></div>
                        </div>

                        <div class="info-grid" style="margin-top: 10px;">
                            <p><strong>Author:</strong> <?= htmlspecialchars($book['author_name']) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($book['status']) ?></p>
                            <p><strong>Updated At:</strong>
                                <?= htmlspecialchars(date('F j, Y', strtotime($book['updated_at']))) ?></p>
                        </div>

                        <div class="description">
                            <strong>Description:</strong>
                            <p class="description-text"><?= limit_words($book['description']) ?></p>
                        </div>

                        <div class="genre-tags">
                            <strong>Genre:</strong>
                            <?php foreach ($genres as $genre): ?>
                            <span class="genre"><?= htmlspecialchars($genre) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="chapter-header">
                    <h2>Chapters</h2>

                    <div class="chapter-buttons">
                        <a href="<?= $first_chapter ? 'chapter.php?id=' . $first_chapter['id'] : '#' ?>"
                            class="chapter-btn">
                            <span>First Chapter</span><br>Chapter
                            <?= $first_chapter ? $first_chapter['chapter_no'] : '-' ?>
                        </a>

                        <a href="<?= $latest_chapter ? 'chapter.php?id=' . $latest_chapter['id'] : '#' ?>"
                            class="chapter-btn">
                            <span>New Chapter</span><br>Chapter
                            <?= $latest_chapter ? $latest_chapter['chapter_no'] : '-' ?>
                        </a>
                    </div>

                    <div class="chapter-search">
                        <input type="number" id="chapterSearch" placeholder="Search Chapter. Example: 25 or 178">
                    </div>
                </div>

                <div id="chapterList" style="max-height: 500px; overflow-y: auto; margin-top: 30px;">
                    <?php foreach ($chapters as $chapter): ?>
                    <div class="chapter-card" data-chapterno="<?= (int)$chapter['chapter_no'] ?>">
                        <a href="chapter.php?id=<?= (int)$chapter['id'] ?>">
                            | Chapter <?= (int)$chapter['chapter_no'] ?>
                            <?php if (!empty(trim($chapter['chapter_name']))): ?>
                            -- <?= htmlspecialchars($chapter['chapter_name']) ?>
                            <?php endif; ?>
                            <span><?= htmlspecialchars(date('F j, Y', strtotime($chapter['updated_at']))) ?></span>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pass user login and book id to JS -->
            <script>
            window.bookData = {
                bookId: <?= json_encode($book_id) ?>,
                isLoggedIn: <?= json_encode(isLoggedIn()) ?>
            };
            </script>

            <script src="assets/js/book.js"></script>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>