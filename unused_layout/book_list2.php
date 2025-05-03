<?php

// include '../includes/admin_navbar.php';
// include '../auth_checks/admin_auth.php';
include '../layout/layout.php';


function head()
{?>
<title>All Books</title>

<link rel="stylesheet" href="../assets/css/all_books.css">
<?php
}
function body()
{?>
<div class="table-wrapper">
    <h2>📚 All Books</h2>

    <table>
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
        <tbody>
            <?php
        include '../includes/db.php';
        $res = $conn->query("SELECT * FROM books");
        while($book = $res->fetch_assoc()):
        ?>
            <tr>
                <td><?= htmlspecialchars($book['id']) ?></td>
                <td><img src="../assets/images/books/<?= htmlspecialchars($book['image']) ?>" alt="Book Cover"
                        class="book-thumb">
                </td>
                <td><?= htmlspecialchars(strlen($book['title']) > 20 ? substr($book['title'], 0, 20) . "..." : $book['title']) ?>
                </td>
                <td><?= htmlspecialchars(strlen($book['description']) > 30 ? substr($book['description'], 0, 30) . "..." : $book['description']) ?>
                </td>
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
</div>
<?php
}
base_layout(body(),head());