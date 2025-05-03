<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$limit = 30;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$selected_genre = $_GET['genre'] ?? 'all';
$selected_status = $_GET['status'] ?? 'all';
$order_by = $_GET['order_by'] ?? 'a-z';

$genres_result = $conn->query("SELECT * FROM genres");
$genres = $genres_result->fetch_all(MYSQLI_ASSOC);

// Build WHERE clause
$where = [];
$params = [];
$types = "";

if ($selected_genre !== 'all') {
    $where[] = "b.id IN (SELECT book_id FROM book_genres WHERE genre_id = ?)";
    $params[] = $selected_genre;
    $types .= "i";
}
if ($selected_status !== 'all') {
    $where[] = "b.status = ?";
    $params[] = $selected_status;
    $types .= "s";
}
$where_sql = $where ? "WHERE " . implode(" AND ", $where) : "";

// Order clause
$order_sql = match ($order_by) {
    'most_viewed' => "ORDER BY total_views DESC",
    'random' => "ORDER BY RAND()",
    'highest_chapter' => "ORDER BY total_chapters DESC",
    'lowest_chapter' => "ORDER BY total_chapters ASC",
    'latest_update' => "ORDER BY latest_chapter_updated_at DESC",
    default => "ORDER BY b.title ASC"
};

// Fetch books
$sql = "
    SELECT 
        b.*, 
        a.name AS author_name,
        COALESCE(SUM(c.views), 0) AS total_views,
        COUNT(c.id) AS total_chapters,
        MAX(c.updated_at) AS latest_chapter_updated_at,
        (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no
    FROM books b
    JOIN authors a ON b.author_id = a.id
    LEFT JOIN chapters c ON b.id = c.book_id
    $where_sql
    GROUP BY b.id
    $order_sql
    LIMIT $limit OFFSET $offset
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$books = $stmt->get_result();

// Count for pagination
$count_sql = "
    SELECT COUNT(DISTINCT b.id) AS total
    FROM books b
    LEFT JOIN book_genres bg ON b.id = bg.book_id
    $where_sql
";
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_books = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_books / $limit);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Browse Books - BookSite</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/browse.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="layout-wrapper">
        <main id="content">
            <div class="section">
                <h2>📚 Browse Books</h2>
                <hr>

                <form method="GET" class="filter-form">
                    <select name="genre" class="dropdown genre-dropdown">
                        <option value="all">Genre: All</option>
                        <?php foreach ($genres as $genre): ?>
                        <option value="<?= $genre['id'] ?>" <?= ($selected_genre == $genre['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genre['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status" class="dropdown">
                        <option value="all">Status: All</option>
                        <option value="Ongoing" <?= $selected_status == 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
                        <option value="Completed" <?= $selected_status == 'Completed' ? 'selected' : '' ?>>Completed
                        </option>
                        <option value="Hiatus" <?= $selected_status == 'Hiatus' ? 'selected' : '' ?>>Hiatus</option>
                    </select>

                    <select name="order_by" class="dropdown">
                        <option value="a-z" <?= $order_by == 'a-z' ? 'selected' : '' ?>>A-Z</option>
                        <option value="most_viewed" <?= $order_by == 'most_viewed' ? 'selected' : '' ?>>Most Viewed
                        </option>
                        <option value="random" <?= $order_by == 'random' ? 'selected' : '' ?>>Random</option>
                        <option value="highest_chapter" <?= $order_by == 'highest_chapter' ? 'selected' : '' ?>>Highest
                            Chapters</option>
                        <option value="lowest_chapter" <?= $order_by == 'lowest_chapter' ? 'selected' : '' ?>>Lowest
                            Chapters</option>
                        <option value="latest_update" <?= $order_by == 'latest_update' ? 'selected' : '' ?>>Latest
                            Update</option>
                    </select>

                    <button type="submit" class="search-button">Search</button>
                </form>

                <?php if ($books && $books->num_rows > 0): ?>
                <div class="book-container">
                    <?php while ($book = $books->fetch_assoc()): ?>
                    <div class="book-card">
                        <a href="book.php?id=<?= $book['id'] ?>" class="book-link">
                            <div class="book-image-wrapper">
                                <img src="assets/images/books/<?= htmlspecialchars($book['image']) ?>" alt="Book Cover">
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

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&<?= http_build_query($_GET) ?>" class="purple-btn">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&<?= http_build_query($_GET) ?>"
                        class="purple-btn <?= $i === $page ? 'active-page' : '' ?>">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page + 1 ?>&<?= http_build_query($_GET) ?>" class="purple-btn">Next</a>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="empty-recommend">No books available.</div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>