<?php
include '../includes/db.php';
include '../auth_checks/admin_auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>All Books - Admin</title>
    <link rel="stylesheet" href="../assets/css/all_books.css">
    <style>
    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .header-row h2 {
        margin: 0;
        font-size: 24px;
    }

    .search-bar-wrapper {
        max-width: 300px;
        position: relative;
    }

    .search-bar-wrapper::before {
        content: "🔍";
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 16px;
        color: #888;
    }

    .search-bar-wrapper input {
        width: 100%;
        padding: 8px 14px 8px 38px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 8px;
    }

    .highlight {
        background-color: rgba(255, 255, 0, 0.4);
        border-radius: 4px;
        padding: 0 0.25px;
    }

    .no-books-message {
        text-align: center;
        color: #777;
        margin-top: 20px;
        display: none;
    }
    </style>
</head>

<body>
    <?php include '../includes/admin_navbar.php'; ?>

    <div class="table-wrapper">
        <div class="header-row">
            <h2>📚 All Books</h2>
            <div class="search-bar-wrapper">
                <input type="text" id="bookSearch" placeholder="Search by title...">
            </div>
        </div>

        <table id="bookTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Chapters</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="bookTableBody">
                <?php
                $res = $conn->query("SELECT * FROM books");
                while($book = $res->fetch_assoc()):
                $title = htmlspecialchars($book['title']);
                $desc = htmlspecialchars($book['description']);
                ?>
                <tr class="book-row">
                    <td><?= htmlspecialchars($book['id']) ?></td>
                    <td><img src="../assets/images/books/<?= htmlspecialchars($book['image']) ?>" alt="Book Cover"
                            class="book-thumb"></td>
                    <td class="book-title" data-title="<?= strtolower($title) ?>">
                        <?= strlen($title) > 20 ? substr($title, 0, 20) . "..." : $title ?>
                    </td>
                    <td><?= strlen($desc) > 30 ? substr($desc, 0, 30) . "..." : $desc ?></td>
                    <td>
                        <a href="chapter/view_chapters.php?book_id=<?= $book['id'] ?>" class="action-link view-link">👁️
                            View</a>
                        <a href="chapter/add_chapter.php?book_id=<?= $book['id'] ?>" class="action-link add-link">➕
                            Add</a>
                    </td>
                    <td>
                        <a href="edit_book.php?id=<?= $book['id'] ?>" class="action-link edit-link">✏️ Edit</a>
                        <a href="delete_book.php?id=<?= $book['id'] ?>"
                            onclick="return confirm('Are you sure you want to delete this book?')"
                            class="action-link delete-link">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="no-books-message">No books match your search. Showing all books.</div>
    </div>

    <?php include 'admin_footer.php'; ?>

    <script>
    const input = document.getElementById('bookSearch');
    const rows = document.querySelectorAll('.book-row');
    const noMatchMsg = document.querySelector('.no-books-message');

    function highlight(text, keyword) {
        if (!keyword) return text;
        const regex = new RegExp(`(${keyword})`, 'ig');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }

    input.addEventListener('input', () => {
        const value = input.value.trim().toLowerCase();
        let matches = 0;

        rows.forEach(row => {
            const titleCell = row.querySelector('.book-title');
            const title = titleCell.getAttribute('data-title');
            const plain = titleCell.textContent;

            if (value === '') {
                // No input: show all and reset highlights
                row.style.display = '';
                titleCell.innerHTML = plain;
                matches++;
            } else if (title.startsWith(value)) {
                row.style.display = '';
                titleCell.innerHTML = highlight(plain, value);
                matches++;
            } else {
                row.style.display = 'none';
            }
        });

        // ❗️If no match found, revert to showing all
        if (matches === 0) {
            rows.forEach(row => {
                const titleCell = row.querySelector('.book-title');
                row.style.display = '';
                titleCell.innerHTML = titleCell.textContent; // reset highlight
            });
        }

        // Optional message toggle
        noMatchMsg.style.display = (matches === 0) ? 'none' : 'none'; // never show it now
    });
    </script>

</body>

</html>