<?php
// components/book_card.php
// $book should be available
if (!isset($book)) return;
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
                <?php if (isset($book['total_views'])): ?>
                <div><span>Total Views:</span> <?= (int)$book['total_views'] ?></div>
                <?php endif; ?>
                <div><span>Latest Chapter:</span> <?= $book['latest_chapter_no'] ?? 'N/A' ?></div>
            </div>
        </div>
    </a>
</div>