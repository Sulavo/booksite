<?php
session_start();
include 'includes/db.php';
include 'includes/navbar.php';

$redirect = $_GET['redirect'] ?? 'index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $redirect = $_POST['redirect'] ?? 'index.php';

    $_SESSION['login_form'] = ['email' => $email, 'redirect' => $redirect];

    $stmt = $conn->prepare("SELECT id, username, password, role, is_banned FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $username, $hashed_password, $role, $is_banned);
        $stmt->fetch();

        if ($is_banned) {
            $_SESSION['login_error'] = "You've been banned from the site.";
        } else {
            $valid = false;

            if (password_verify($password, $hashed_password)) {
                $valid = true;
            } elseif ($password === $hashed_password) {
                $valid = true;
                $rehash = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->bind_param("si", $rehash, $id);
                $update->execute();
                $update->close();
            }

            if ($valid) {
                $_SESSION['user'] = [
                    'id' => $id,
                    'username' => $username,
                    'role' => $role
                ];
                unset($_SESSION['login_error'], $_SESSION['login_form']);
                header("Location: " . ($role == 0 ? "admin/book_list.php" : $redirect));
                exit();
            } else {
                $_SESSION['login_error'] = "Incorrect email or password.";
            }
        }
    } else {
        $_SESSION['login_error'] = "User not found.";
    }

    header("Location: login.php" . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
    exit();
}

$error = $_SESSION['login_error'] ?? '';
$old = $_SESSION['login_form'] ?? ['email' => '', 'redirect' => $redirect];
unset($_SESSION['login_error'], $_SESSION['login_form']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <style>
    html,
    body {
        height: 100%;
        margin: 0;
        padding: 0;
    }

    body {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    #content {
        flex: 1;
    }

    .error {
        color: red;
        font-size: 0.9em;
        margin-top: 5px;
    }
    </style>
</head>

<body>
    <main id="content">
        <div class="page-wrapper">
            <div class="main-content">
                <div class="login-container">
                    <h2>Login to Your Account</h2>
                    <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <form class="login-form" method="POST" onsubmit="validateLoginForm(event)">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($old['redirect']) ?>">

                        <label for="email">Email Address</label>
                        <input type="text" id="email" name="email" placeholder="Enter your email" required
                            value="<?= htmlspecialchars($old['email']) ?>">
                        <div id="email-error" class="error"></div>

                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <div id="password-error" class="error"></div>

                        <button type="submit" class="login-btn">Login</button>
                    </form>

                    <p class="signup-link">
                        Don't have an account?
                        <a
                            href="register.php<?= !empty($old['redirect']) ? '?redirect=' . urlencode($old['redirect']) : '' ?>">
                            Sign up here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function validateLoginForm(event) {
        let isValid = true;
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;

        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        document.getElementById("email-error").textContent = "";
        document.getElementById("password-error").textContent = "";

        if (email === "") {
            document.getElementById("email-error").textContent = "Email is required.";
            isValid = false;
        } else if (!emailPattern.test(email)) {
            document.getElementById("email-error").textContent = "Enter a valid email address.";
            isValid = false;
        }

        if (password === "") {
            document.getElementById("password-error").textContent = "Password is required.";
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    }
    </script>
</body>

</html>