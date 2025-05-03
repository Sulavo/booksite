<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'book_db');
if ($conn->connect_error) {
    die("❌ DB Connection Failed: " . $conn->connect_error);
}

include '../includes/admin_navbar.php';
include '../auth_checks/admin_auth.php';

$errors = [];
$success = "";

// Fetch all genres
$genreResult = $conn->query("SELECT id, name FROM genres");
$genres = $genreResult ? $genreResult->fetch_all(MYSQLI_ASSOC) : [];

$imageUploadDir = "../assets/images/books/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author_name = trim($_POST['author_name']);
    $description = trim($_POST['description']);
    $status = trim($_POST['status']);
    $released_on = $_POST['released_on'];
    $genre_ids = isset($_POST['genres']) ? $_POST['genres'] : [];

    // Validate form inputs
    if (empty($title) || empty($author_name) || empty($description) || empty($status) || empty($released_on) || empty($genre_ids)) {
        $errors[] = "❌ All fields are required and at least one genre must be selected.";
    }

    $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $imageName = $_FILES['image']['name'];
        $imageTmp = $_FILES['image']['tmp_name'];
        $imageExt = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

        if (!in_array($imageExt, $allowed_ext)) {
            $errors[] = "❌ Only JPG, JPEG, PNG, WEBP, and GIF formats are allowed.";
        }
    } else {
        $errors[] = "❌ Please upload a book image.";
    }

    if (empty($errors)) {
        // Check if the author already exists
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

        // Handle image upload
        $uniqueImageName = uniqid('book_', true) . '.' . $imageExt;
        $targetPath = $imageUploadDir . $uniqueImageName;

        if (!file_exists($imageUploadDir)) {
            mkdir($imageUploadDir, 0755, true);
        }

        if (move_uploaded_file($imageTmp, $targetPath)) {
            // Insert book details
            $stmt = $conn->prepare("INSERT INTO books (title, author_id, description, image, status, released_on) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sissss", $title, $author_id, $description, $uniqueImageName, $status, $released_on);

            if ($stmt->execute()) {
                $book_id = $stmt->insert_id;

                // Insert genres
                $genre_stmt = $conn->prepare("INSERT INTO book_genres (book_id, genre_id) VALUES (?, ?)");
                foreach ($genre_ids as $gid) {
                    $genre_stmt->bind_param("ii", $book_id, $gid);
                    $genre_stmt->execute();
                }

                $success = "✅ Book inserted successfully!";
            } else {
                $errors[] = "❌ Failed to insert book: " . $stmt->error;
            }
        } else {
            $errors[] = "❌ Failed to move uploaded image. Check file permissions.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Insert Book</title>
    <link rel="stylesheet" href="../assets/css/insert_book.css">
</head>

<body style="display: flex; flex-direction: column; min-height: 100vh; margin: 0;">
    <main id="content" style="flex: 1;">
        <div class="insert-book-container">
            <h2>➕ Insert New Book</h2>

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
                <label>Upload Book Image</label>
                <input type="file" name="image" accept="image/*" required>

                <label>Book Title</label>
                <input type="text" name="title" required>

                <label>Author Name</label>
                <input type="text" name="author_name" required>

                <label>Description</label>
                <textarea name="description" rows="4" required></textarea>

                <label>Genres</label>
                <div class="genre-checkboxes">
                    <?php foreach ($genres as $genre): ?>
                    <label>
                        <input type="checkbox" name="genres[]" value="<?= $genre['id'] ?>">
                        <?= htmlspecialchars($genre['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <label>Status</label>
                <input type="text" name="status" placeholder="e.g. Ongoing, Completed" required>

                <label>Released Date</label>
                <input type="date" name="released_on" required>

                <button type="submit">📚 Add Book</button>
            </form>
        </div>
    </main>

    <?php include 'admin_footer.php'; ?>
</body>

</html>