<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';


$limit = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;


if (!isLoggedIn()) {
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Bookmarks - BookSite</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="layout-wrapper">
        <main id="content">
            <section class="section">
                <div class="empty-recommend">
                    <h2>🔖 Your Bookmarks</h2>
                    <p>To bookmark a book you need to login first.</p>
                    <a href="login.php" class="purple-btn">Login</a>
                </div>
            </section>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</body>

</html>
<?php
    exit();
}


$userId = getUserId();


$stmt = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM bookmarks
    WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$total_bookmarks = $result->fetch_assoc()['total'] ?? 0;
$total_pages = max(ceil($total_bookmarks / $limit), 1);


$stmt = $conn->prepare("
    SELECT b.*, a.name AS author_name,
           COALESCE(COUNT(cv.id), 0) AS total_views,
           (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no
    FROM bookmarks bm
    JOIN books b ON bm.book_id = b.id
    JOIN authors a ON b.author_id = a.id
    LEFT JOIN chapters c ON b.id = c.book_id
    LEFT JOIN chapter_views cv ON c.id = cv.chapter_id
    WHERE bm.user_id = ?
    GROUP BY b.id
    ORDER BY bm.created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $userId, $limit, $offset);
$stmt->execute();
$bookmarks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Your Bookmarks - BookSite</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="layout-wrapper">
        <main id="content">
            <section class="section">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <h2 style="margin: 0;">🔖 Your Bookmarks</h2>
                </div>

                <?php if ($bookmarks && $bookmarks->num_rows > 0): ?>
                <div class="book-container">
                    <?php while ($book = $bookmarks->fetch_assoc()): ?>
                    <div class="book-card">
                        <a href="book.php?id=<?= htmlspecialchars($book['id']) ?>" class="book-link">
                            <div class="book-image-wrapper">
                                <img src="assets/images/books/<?= htmlspecialchars($book['image'] ?: 'default.png') ?>"
                                    alt="Book Cover">
                                <div class="status-badge"><?= htmlspecialchars($book['status']) ?></div>
                                <div class="view-more">View More</div>
                            </div>
                            <div class="book-info">
                                <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                                <div class="book-meta">
                                    <div><span>Author:</span> <?= htmlspecialchars($book['author_name']) ?></div>
                                    <div><span>Total Views:</span> <?= (int)$book['total_views'] ?></div>
                                    <div><span>Latest Chapter:</span> <?= $book['latest_chapter_no'] ?? 'N/A' ?></div>
                                </div>
                            </div>
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="pagination" style="margin-top: 30px; text-align: center;">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>" class="purple-btn">Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" class="purple-btn <?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>" class="purple-btn">Next</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="empty-recommend">You have no bookmarks yet.</div>
                <?php endif; ?>
            </section>
        </main>
        <?php include 'includes/footer.php'; ?>
    </div>
</body>

</html>