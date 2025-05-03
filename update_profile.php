<?php
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = "";

// Fetch user from DB
$stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldPassword = trim($_POST['old_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword || $confirmPassword) {
        if (empty($oldPassword)) {
            $errors[] = "Old password is required to change password.";
        } elseif (!password_verify($oldPassword, $user['password'])) {
            $errors[] = "Old password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = "New password and confirmation do not match.";
        } else {
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed, $user_id);
            if ($stmt->execute()) {
                $success = "✅ Password updated successfully.";
            } else {
                $errors[] = "❌ Failed to update password.";
            }
        }
    } else {
        $errors[] = "No changes made.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Update Profile</title>
    <link rel="stylesheet" href="assets/css/update_profile.css">
</head>

<body>
    <main class="profile-wrapper">
        <div class="profile-card">
            <h2>👤 Update Profile</h2>

            <?php if (!empty($errors)): ?>
            <div class="error-box">
                <?php foreach ($errors as $e): ?>
                <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
            <div class="success-box"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" class="profile-form" oninput="toggleButton()">
                <label>Username (cannot change)</label>
                <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled>

                <label>Email (cannot change)</label>
                <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled>

                <label>Old Password</label>
                <input type="password" name="old_password" placeholder="Enter old password">

                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password">

                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password">

                <div class="btn-group">
                    <button type="submit" id="updateBtn" class="update-btn" disabled>🔒 Update Profile</button>
                    <a href="delete_user.php" class="delete-btn"
                        onclick="return confirm('Are you sure? This cannot be undone.')">🗑️ Delete Account</a>
                </div>
            </form>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function toggleButton() {
        const oldPass = document.querySelector('input[name="old_password"]').value;
        const newPass = document.querySelector('input[name="new_password"]').value;
        const confirm = document.querySelector('input[name="confirm_password"]').value;
        document.getElementById('updateBtn').disabled = !(oldPass && (newPass || confirm));
    }
    </script>
</body>

</html>