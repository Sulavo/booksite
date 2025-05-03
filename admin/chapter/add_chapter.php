<?php
include '../../includes/db.php';
include '../../includes/admin_navbar.php';
include '../../auth_checks/admin_auth.php';

if (!isset($_GET['book_id']) || empty($_GET['book_id'])) {
    die('Book ID is missing.');
}

$book_id = intval($_GET['book_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $chapter_no = trim($_POST['chapter_no']);
    $chapter_name = trim($_POST['chapter_name']);
    $content = trim($_POST['content']);

    if (empty($chapter_no) || !is_numeric($chapter_no)) {
        $errors[] = "Chapter number must be a valid number.";
    }
    if (empty($content)) {
        $errors[] = "Content cannot be empty.";
    }

    if (empty($errors)) {
        $checkChapterNo = $conn->prepare("SELECT id FROM chapters WHERE book_id = ? AND chapter_no = ?");
        $checkChapterNo->bind_param("ii", $book_id, $chapter_no);
        $checkChapterNo->execute();
        $checkChapterNo->store_result();

        if ($checkChapterNo->num_rows > 0) {
            $errors[] = "❌ A chapter with this number already exists for this book.";
        }

        if (empty($errors)) {
            $checkChapterName = $conn->prepare("SELECT id FROM chapters WHERE book_id = ? AND chapter_name = ?");
            $checkChapterName->bind_param("is", $book_id, $chapter_name);
            $checkChapterName->execute();
            $checkChapterName->store_result();

            if ($checkChapterName->num_rows > 0) {
                $errors[] = "❌ A chapter with this name already exists for this book.";
            }
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO chapters (book_id, chapter_no, chapter_name, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $book_id, $chapter_no, $chapter_name, $content);

        if ($stmt->execute()) {
            header("Location: view_chapters.php?book_id=$book_id&status=chapter_added");
            exit;
        } else {
            $errors[] = "❌ Failed to add chapter. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Add Chapter</title>
    <link rel="stylesheet" href="../../assets/css/add_chapter.css"> <!-- Styles specific to this page -->
</head>

<body>
    <div class="layout-wrapper">
        <main id="content">
            <div class="form-wrapper">
                <h2>➕ Add New Chapter</h2>

                <?php if (!empty($errors)): ?>
                <div class="error-box">
                    <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <form method="POST" class="chapter-form">
                    <div class="left-inputs">
                        <div class="form-group">
                            <label>Chapter Number:</label>
                            <input type="number" name="chapter_no" required>
                        </div>
                        <div class="form-group">
                            <label>Chapter Name:</label>
                            <input type="text" name="chapter_name">
                        </div>
                        <input type="submit" value="Add Chapter" class="submit-btn">
                    </div>
                    <div class="right-content">
                        <label>Content:</label>
                        <textarea name="content" required></textarea>
                    </div>
                </form>
            </div>
        </main>
        <?php include '../admin_footer.php'; ?>
    </div>
</body>

</html>