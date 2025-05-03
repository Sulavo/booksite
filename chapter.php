<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
include 'includes/navbar.php';

// Check chapter id
if (!isset($_GET['id'])) {
    die("Chapter ID not specified.");
}

$chapter_id = intval($_GET['id']);

// Fetch current chapter and its book
$stmt = $conn->prepare("
    SELECT c.id, c.chapter_no, c.chapter_name, c.content, c.book_id, b.title AS book_title 
    FROM chapters c 
    JOIN books b ON c.book_id = b.id 
    WHERE c.id = ?
");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();
$current_chapter = $result->fetch_assoc();

if (!$current_chapter) {
    die("Chapter not found.");
}

// Fetch all chapters of the same book
$stmt_all = $conn->prepare("
    SELECT id, chapter_no, chapter_name 
    FROM chapters 
    WHERE book_id = ? 
    ORDER BY chapter_no ASC
");
$stmt_all->bind_param("i", $current_chapter['book_id']);
$stmt_all->execute();
$chapters_result = $stmt_all->get_result();
$chapters = [];
while ($row = $chapters_result->fetch_assoc()) {
    $chapters[] = $row;
}

$current_index = array_search($chapter_id, array_column($chapters, 'id'));
$prev_chapter = $chapters[$current_index - 1] ?? null;
$next_chapter = $chapters[$current_index + 1] ?? null;

// VIEW TRACKING LOGIC

$book_id = $current_chapter['book_id'];
$user_id = isLoggedIn() ? $_SESSION['user']['id'] : null;
$ip_address = $_SERVER['REMOTE_ADDR'];

// Prepare check
if ($user_id) {
    // Logged-in user: check by user_id
    $stmt_check = $conn->prepare("
        SELECT id FROM chapter_views
        WHERE user_id = ? AND chapter_id IN (
            SELECT id FROM chapters WHERE book_id = ?
        ) AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        LIMIT 1
    ");
    $stmt_check->bind_param("ii", $user_id, $book_id);
} else {
    // Guest: check by IP address
    $stmt_check = $conn->prepare("
        SELECT id FROM chapter_views
        WHERE ip_address = ? AND chapter_id IN (
            SELECT id FROM chapters WHERE book_id = ?
        ) AND viewed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        LIMIT 1
    ");
    $stmt_check->bind_param("si", $ip_address, $book_id);
}

$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows === 0) {
    // No view in last 30 days — count new view

    $stmt_insert = $conn->prepare("
        INSERT INTO chapter_views (chapter_id, user_id, ip_address, viewed_at)
        VALUES (?, ?, ?, NOW())
    ");
    $user_id_param = $user_id ?? null;
    $stmt_insert->bind_param("iis", $chapter_id, $user_id_param, $ip_address);
    $stmt_insert->execute();

    // Update views counter
    $conn->query("UPDATE chapters SET views = views + 1 WHERE id = $chapter_id");
}

$stmt_check->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($current_chapter['book_title']) ?> - Chapter
        <?= htmlspecialchars($current_chapter['chapter_no']) ?></title>
    <link rel="stylesheet" href="assets/css/chapter_style.css">
    <script>
    function jumpToChapter(select) {
        if (select.value) {
            window.location.href = select.value;
        }
    }
    </script>
</head>

<body>
    <div class="layout-wrapper">
        <main id="content">
            <div class="chapter-page">

                <!-- Top Navigation -->
                <div class="chapter-top-nav">
                    <?php if ($prev_chapter): ?>
                    <a href="chapter.php?id=<?= $prev_chapter['id'] ?>"><button>Prev</button></a>
                    <?php else: ?>
                    <button disabled>Prev</button>
                    <?php endif; ?>

                    <select onchange="jumpToChapter(this)">
                        <?php foreach ($chapters as $chapter): ?>
                        <option value="chapter.php?id=<?= $chapter['id'] ?>"
                            <?= ($chapter['id'] == $current_chapter['id']) ? 'selected' : '' ?>>
                            Chapter <?= htmlspecialchars($chapter['chapter_no']) ?> -
                            <?= htmlspecialchars($chapter['chapter_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>

                    <?php if ($next_chapter): ?>
                    <a href="chapter.php?id=<?= $next_chapter['id'] ?>"><button>Next</button></a>
                    <?php else: ?>
                    <button disabled>Next</button>
                    <?php endif; ?>
                </div>

                <!-- Chapter Heading -->
                <div class="chapter-name">
                    <h2>
                        <a class="book-link" href="book.php?id=<?= $current_chapter['book_id'] ?>">
                            <?= htmlspecialchars($current_chapter['book_title']) ?>
                        </a>
                    </h2><br>
                    <h3><?= htmlspecialchars($current_chapter['chapter_name']) ?></h3>
                </div>

                <!-- Chapter Content -->
                <div class="chapter-content">
                    <?= nl2br(htmlspecialchars($current_chapter['content'])) ?>
                </div>

                <!-- Bottom Navigation -->
                <div class="chapter-bottom-nav">
                    <?php if ($prev_chapter): ?>
                    <a href="chapter.php?id=<?= $prev_chapter['id'] ?>"><button>Prev</button></a>
                    <?php else: ?>
                    <button disabled>Prev</button>
                    <?php endif; ?>

                    <?php if ($next_chapter): ?>
                    <a href="chapter.php?id=<?= $next_chapter['id'] ?>"><button>Next</button></a>
                    <?php else: ?>
                    <button disabled>Next</button>
                    <?php endif; ?>
                </div>

            </div>
        </main>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>

</html>