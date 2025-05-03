<?php
include '../../includes/db.php';
include '../../includes/admin_navbar.php';
include '../../auth_checks/admin_auth.php';

if (!isset($_GET['book_id']) || empty($_GET['book_id'])) {
    die('Book ID is missing.');
}

$book_id = intval($_GET['book_id']);
$status = $_GET['status'] ?? null;

$stmt = $conn->prepare("SELECT id, chapter_no, chapter_name FROM chapters WHERE book_id = ? ORDER BY chapter_no ASC");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>View Chapters</title>
    <link rel="stylesheet" href="../../assets/css/view_chapter.css">
</head>

<body>
    <div class="admin-layout-wrapper">
        <?php if ($status == 'chapter_added'): ?>
        <div class="status-toast">✅ Chapter added successfully!</div>
        <?php elseif ($status == 'chapter_deleted'): ?>
        <div class="status-toast">🗑️ Chapter deleted successfully!</div>
        <?php elseif ($status == 'chapter_updated'): ?>
        <div class="status-toast">✏️ Chapter updated successfully!</div>
        <?php endif; ?>

        <main id="content">
            <div class="table-wrapper">
                <div style="text-align:center; margin-bottom: 20px;">
                    <a href="add_chapter.php?book_id=<?= $book_id ?>" class="action-link add-link">➕ Add Chapter</a>
                </div>

                <h2 style="text-align:center; margin-bottom: 10px;">📚 Chapter List</h2><br>

                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Chapter Number</th>
                            <th>Chapter Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <?php 
                                    $chapterName = trim($row['chapter_name']);
                                    $displayName = !empty($chapterName) ? htmlspecialchars($chapterName) : "[No Chapter Name]";
                                ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['chapter_no']) ?></td>
                            <td><?= $displayName ?></td>
                            <td>
                                <a class="action-link edit-link"
                                    href="edit_chapter.php?chapter_id=<?= $row['id'] ?>&book_id=<?= $book_id ?>">✏️Edit</a>
                                <a class="action-link delete-link"
                                    href="delete_chapter.php?chapter_id=<?= $row['id'] ?>&book_id=<?= $book_id ?>"
                                    onclick="return confirm('Are you sure you want to delete this chapter?')">🗑️Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php else: ?>
                        <tr>
                            <td colspan="4">No chapters found for this book.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <?php include '../admin_footer.php'; ?>
    </div>
</body>

</html>