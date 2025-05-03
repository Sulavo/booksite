<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../includes/db.php';
include '../includes/admin_navbar.php';
include '../auth_checks/admin_auth.php';

if (!isset($_GET['id'])) {
    die("❌ Book ID is required.");
}

$book_id = intval($_GET['id']);
$errors = [];
$success = "";

// Fetch book info with author name
$book_stmt = $conn->prepare("SELECT b.*, a.name AS author_name FROM books b 
                             JOIN authors a ON b.author_id = a.id 
                             WHERE b.id = ?");
$book_stmt->bind_param("i", $book_id);
$book_stmt->execute();
$book_result = $book_stmt->get_result();

if ($book_result->num_rows === 0) {
    die("❌ Book not found.");
}

$book = $book_result->fetch_assoc();

// Fetch all genres
$genreResult = $conn->query("SELECT id, name FROM genres");
$genres = $genreResult ? $genreResult->fetch_all(MYSQLI_ASSOC) : [];

// Get book's selected genres
$selected_genres_result = $conn->query("SELECT genre_id FROM book_genres WHERE book_id = $book_id");
$selected_genres = [];
while ($row = $selected_genres_result->fetch_assoc()) {
    $selected_genres[] = $row['genre_id'];
}

$imageUploadDir = "../assets/images/books/";
$currentImage = $book['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);
    $released_on = $_POST['released_on'];
    $genre_ids = isset($_POST['genres']) ? $_POST['genres'] : [];

    if (empty($title) || empty($author_name) || empty($description) || empty($status) || empty($released_on) || empty($genre_ids)) {
        $errors[] = "❌ All fields are required and at least one genre must be selected.";
    }

    $newImageName = $currentImage;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = $_FILES['image']['name'];
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($imageExt, $allowed_ext)) {
            $errors[] = "❌ Invalid image format.";
        } else {
            $newImageName = uniqid('book_', true) . '.' . $imageExt;
            $targetPath = $imageUploadDir . $newImageName;

            if (!file_exists($imageUploadDir)) {
                mkdir($imageUploadDir, 0755, true);
            }

            move_uploaded_file($imageTmp, $targetPath);
        }
    }

    if (empty($errors)) {
        // Get or create author
        $stmt = $conn->prepare("SELECT id FROM authors WHERE name = ?");
        $stmt->bind_param("s", $author_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $insert_author = $conn->prepare("INSERT INTO authors (name) VALUES (?)");
            $insert_author->bind_param("s", $author_name);
            $insert_author->execute();
            $author_id = $insert_author->insert_id;
        } else {
            $stmt->bind_result($author_id);
            $stmt->fetch();
        }

        $update = $conn->prepare("UPDATE books SET title = ?, author_id = ?, description = ?, image = ?, status = ?, released_on = ? WHERE id = ?");
        $update->bind_param("sissssi", $title, $author_id, $description, $newImageName, $status, $released_on, $book_id);

        if ($update->execute()) {
            $conn->query("DELETE FROM book_genres WHERE book_id = $book_id");

            $genre_stmt = $conn->prepare("INSERT INTO book_genres (book_id, genre_id) VALUES (?, ?)");
            foreach ($genre_ids as $gid) {
                $genre_stmt->bind_param("ii", $book_id, $gid);
                $genre_stmt->execute();
            }

            $success = "✅ Book updated successfully!";
            $selected_genres = $genre_ids;
            $currentImage = $newImageName;
        } else {
            $errors[] = "❌ Failed to update book.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="../assets/css/insert_book.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">
    <main id="content" style="flex: 1;">
        <div class="insert-book-container">
            <h2>✏️ Edit Book</h2>

            <?php if (!empty($errors)): ?>
            <div class="error-msg">
                <ul>
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <p class="success-msg"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <label>Current Image</label><br>
                <img src="<?= $imageUploadDir . htmlspecialchars($currentImage) ?>" alt="Book Image"
                    style="max-width:150px; border:1px solid #ccc; margin-bottom:10px;"><br>

                <label>Upload New Image (optional)</label>
                <input type="file" name="image" accept="image/*">

                <label>Book Title</label>
                <input type="text" name="title" value="<?= htmlspecialchars($book['title']) ?>" required>

                <label>Author Name</label>
                <input type="text" name="author_name" value="<?= htmlspecialchars($book['author_name']) ?>" required>

                <label>Description</label>
                <textarea name="description" rows="4" required><?= htmlspecialchars($book['description']) ?></textarea>

                <label>Genres</label>
                <div class="genre-checkboxes">
                    <?php foreach ($genres as $genre): ?>
                    <label>
                        <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>"
                            <?= in_array($genre['id'], $selected_genres) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($genre['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <label>Status</label>
                <input type="text" name="status" value="<?= htmlspecialchars($book['status']) ?>" required>

                <label>Released Date</label>
                <input type="date" name="released_on" value="<?= $book['released_on'] ?>" required>

                <button type="submit">💾 Update Book</button>
            </form>
        </div>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>

</html>