<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// ========== TF-IDF Helper Functions ==========
function tokenize($text) {
    return preg_split('/[\s]+/', strtolower(strip_tags($text)));
}

function computeTFIDF($documents) {
    $tf = [];
    $df = [];

    foreach ($documents as $docId => $words) {
        $counts = array_count_values($words);
        $tf[$docId] = [];
        foreach ($counts as $word => $count) {
            $tf[$docId][$word] = $count / count($words);
            $df[$word] = ($df[$word] ?? 0) + 1;
        }
    }

    $N = count($documents);
    $tfidf = [];
    foreach ($tf as $docId => $terms) {
        $tfidf[$docId] = [];
        foreach ($terms as $word => $termFreq) {
            $idf = log($N / $df[$word]);
            $tfidf[$docId][$word] = $termFreq * $idf;
        }
    }
    return $tfidf;
}

function cosineSimilarity($vec1, $vec2) {
    $dot = 0.0;
    $mag1 = 0.0;
    $mag2 = 0.0;

    foreach ($vec1 as $key => $value) {
        $dot += $value * ($vec2[$key] ?? 0);
        $mag1 += $value ** 2;
    }
    foreach ($vec2 as $value) {
        $mag2 += $value ** 2;
    }

    if ($mag1 == 0 || $mag2 == 0) return 0.0;
    return $dot / (sqrt($mag1) * sqrt($mag2));
}

function getTFIDFRecommendedBooks($conn, $user_id, $limit = 21) {
    $allBooks = [];
    $bookMeta = [];

    $res = $conn->query("
        SELECT b.id, b.title, b.description, b.image, b.status,
               a.name AS author_name,
               (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no,
               (SELECT SUM(views) FROM chapters WHERE book_id = b.id) AS total_views
        FROM books b
        JOIN authors a ON b.author_id = a.id
    ");
    while ($row = $res->fetch_assoc()) {
        $book_id = $row['id'];
        $allBooks[$book_id] = tokenize($row['description']);
        $bookMeta[$book_id] = $row;
    }

    if (empty($allBooks)) return [];

    $tfidf = computeTFIDF($allBooks);

    $viewed = [];
    $viewRes = $conn->prepare("
        SELECT DISTINCT b.id FROM books b
        JOIN chapters c ON b.id = c.book_id
        JOIN chapter_views cv ON c.id = cv.chapter_id
        WHERE cv.user_id = ?
    ");
    $viewRes->bind_param("i", $user_id);
    $viewRes->execute();
    $viewResult = $viewRes->get_result();
    while ($row = $viewResult->fetch_assoc()) {
        $viewed[] = $row['id'];
    }

    if (empty($viewed)) return [];

    $userVec = [];
    foreach ($viewed as $vid) {
        foreach ($tfidf[$vid] ?? [] as $word => $value) {
            $userVec[$word] = ($userVec[$word] ?? 0) + $value;
        }
    }
    foreach ($userVec as $word => &$value) {
        $value /= count($viewed);
    }

    $scores = [];
    foreach ($tfidf as $book_id => $vec) {
        $scores[$book_id] = cosineSimilarity($userVec, $vec);
    }

    arsort($scores);
    $recommended = [];
    foreach ($scores as $book_id => $score) {
        if ($score >= 0.1) {
            $recommended[] = $bookMeta[$book_id];
            if (count($recommended) >= $limit) break;
        }
    }
    return $recommended;
}

function getPopularBooks($conn) {
    return $conn->query("
        SELECT b.*, a.name AS author_name,
               COALESCE(SUM(c.views), 0) AS total_views,
               (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no
        FROM books b
        JOIN authors a ON b.author_id = a.id
        LEFT JOIN chapters c ON b.id = c.book_id
        GROUP BY b.id
        HAVING total_views > 0
        ORDER BY total_views DESC
        LIMIT 14
    ");
}

function getLastUpdatedBooks($conn) {
    return $conn->query("
        SELECT b.*, a.name AS author_name,
               COALESCE(SUM(c.views), 0) AS total_views,
               (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no
        FROM books b
        JOIN authors a ON b.author_id = a.id
        LEFT JOIN chapters c ON b.id = c.book_id
        GROUP BY b.id
        ORDER BY b.updated_at DESC
        LIMIT 14
    ");
}

function getPopularTodayBooks($conn) {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT b.*, a.name AS author_name,
               COUNT(cv.id) AS total_views,
               (SELECT chapter_no FROM chapters WHERE book_id = b.id ORDER BY created_at DESC LIMIT 1) AS latest_chapter_no
        FROM books b
        JOIN authors a ON b.author_id = a.id
        JOIN chapters c ON b.id = c.book_id
        JOIN chapter_views cv ON c.id = cv.chapter_id
        WHERE DATE(cv.viewed_at) = ?
        GROUP BY b.id
        ORDER BY total_views DESC
        LIMIT 14
    ");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    return $stmt->get_result();
}

$popular_books = getPopularBooks($conn);
$updated_books = getLastUpdatedBooks($conn);
$popular_today_books = getPopularTodayBooks($conn);

$recommended_books = [];
$recommendation_message = '';

if (isLoggedIn()) {
    $user_id = $_SESSION['user']['id'];
    $recommended_books = getTFIDFRecommendedBooks($conn, $user_id);
    if (empty($recommended_books)) {
        $recommendation_message = "<p>No personalized recommendations yet. View a few chapters first.</p>";
    }
} else {
    $recommendation_message = "<p>Login to get personalized recommendations.</p><a href='login.php' class='purple-btn'>Login</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Home - BookSite</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/home.css">
</head>

<body>
    <div id="content">
        <?php include 'includes/navbar.php'; ?>

        <?php
        function displaySection($title, $books, $empty_message = "No books found.") {
            echo "<div class='section'><h2>{$title}</h2>";
            if (!empty($books)) {
                echo "<div class='book-container'>";
                foreach ($books as $book) {
        ?>
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
                        <div><span>Total Views:</span> <?= (int)($book['total_views'] ?? 0) ?></div>
                        <div><span>Latest Chapter:</span>
                            <?= $book['latest_chapter_no'] ? (int)$book['latest_chapter_no'] : 'N/A' ?></div>
                    </div>
                </div>
            </a>
        </div>
        <?php
                }
                echo "</div>";
            } else {
                echo "<div class='empty-recommend'>{$empty_message}</div>";
            }
            echo "</div>";
        }

        displaySection("🔥 Popular Books", $popular_books->fetch_all(MYSQLI_ASSOC));
        displaySection("📘 Latest Updates", $updated_books->fetch_all(MYSQLI_ASSOC));
        displaySection("🔥 Popular Today", $popular_today_books->fetch_all(MYSQLI_ASSOC));
        displaySection("🎯 Recommended for You", $recommended_books, $recommendation_message);
        ?>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>