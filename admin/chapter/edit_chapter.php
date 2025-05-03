<?php
include '../../includes/db.php';
include '../../includes/admin_navbar.php';
include '../../auth_checks/admin_auth.php';

if (!isset($_GET['chapter_id'], $_GET['book_id'])) {
    die("Missing chapter or book ID.");
}

$chapter_id = intval($_GET['chapter_id']);
$book_id = intval($_GET['book_id']);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $stmt = $conn->prepare("UPDATE chapters SET chapter_no = ?, chapter_name = ?, content = ? WHERE id = ?");
        $stmt->bind_param("issi", $chapter_no, $chapter_name, $content, $chapter_id);
        if ($stmt->execute()) {
            header("Location: view_chapters.php?book_id=$book_id&status=chapter_updated");
            exit;
        } else {
            $errors[] = "❌ Failed to update chapter. Please try again.";
        }
    }
}

$stmt = $conn->prepare("SELECT chapter_no, chapter_name, content FROM chapters WHERE id = ?");
$stmt->bind_param("i", $chapter_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Chapter not found.");
}
$chapter = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Chapter</title>
    <link rel="stylesheet" href="../../assets/css/add_chapter.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">
    <main id="content" style="flex: 1;">
        <div class="layout-wrapper">
            <div class="form-wrapper">
                <h2>✏️ Edit Chapter</h2>

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
                            <input type="number" name="chapter_no" required
                                value="<?= htmlspecialchars($chapter['chapter_no']) ?>">
                        </div>
                        <div class="form-group">
                            <label>Chapter Name:</label>
                            <input type="text" name="chapter_name"
                                value="<?= htmlspecialchars($chapter['chapter_name']) ?>">
                        </div>
                        <input type="submit" value="Update Chapter" class="submit-btn">
                    </div>

                    <div class="right-content">
                        <label>Content:</label>
                        <textarea name="content" required><?= htmlspecialchars($chapter['content']) ?></textarea>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include '../admin_footer.php'; ?>
</body>

</html>