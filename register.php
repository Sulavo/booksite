<?php
include 'includes/db.php';
session_start();

$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = $_POST['redirect'] ?? '';
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'redirect' => $redirect
    ];

    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error'] = "Email already registered.";
        } else {
            $checkName = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $checkName->bind_param("s", $name);
            $checkName->execute();
            $checkName->store_result();
            if ($checkName->num_rows > 0) {
                $_SESSION['error'] = "Username already exists. Try a new Username.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $name, $email, $hashed_password);
                if ($insert->execute()) {
                    $user_id = $insert->insert_id;
                    $_SESSION['user'] = [
                        'id' => $user_id,
                        'username' => $name,
                        'email' => $email,
                        'role' => 1
                    ];
                    unset($_SESSION['form_data'], $_SESSION['error']);
                    header("Location: " . (!empty($redirect) ? $redirect : 'index.php'));
                    exit();
                } else {
                    $_SESSION['error'] = "Registration failed. Try again.";
                }
            }
        }
    }

    header("Location: register.php" . (!empty($redirect) ? '?redirect=' . urlencode($redirect) : ''));
    exit();
}

$old = $_SESSION['form_data'] ?? ['name' => '', 'email' => '', 'redirect' => $redirect];
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error'], $_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Signup</title>
    <link rel="stylesheet" href="assets/css/signup.css">
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
    <?php include 'includes/navbar.php'; ?>

    <main id="content">
        <div class="page-wrapper">
            <div class="main-content">
                <div class="signup-container">
                    <h2>Create an Account</h2><br>
                    <?php if (!empty($error)): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>
                    <form class="signup-form" method="POST" onsubmit="validateForm(event)">
                        <input type="hidden" name="redirect" value="<?= htmlspecialchars($old['redirect']) ?>">

                        <label for="name">Username</label>
                        <input type="text" id="name" name="name" required placeholder="Enter your Username"
                            value="<?= htmlspecialchars($old['name']) ?>">
                        <div id="name-error" class="error"></div>

                        <label for="email">Email Address</label>
                        <input type="text" id="email" name="email" required placeholder="Enter your email"
                            value="<?= htmlspecialchars($old['email']) ?>">
                        <div id="email-error" class="error"></div>

                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Create a password">
                        <div id="password-error" class="error"></div>

                        <label for="confirm-password">Confirm Password</label>
                        <input type="password" id="confirm-password" name="confirm_password" required
                            placeholder="Confirm your password">
                        <div id="confirm-password-error" class="error"></div>

                        <button type="submit" class="signup-btn">Sign Up</button>
                    </form>

                    <p class="login-link">
                        Already have an account?
                        <a
                            href="login.php<?= !empty($old['redirect']) ? '?redirect=' . urlencode($old['redirect']) : '' ?>">
                            Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
    function validateForm(event) {
        let isValid = true;
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.trim();
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm-password").value;

        const namePattern = /^[A-Za-z\s]+$/;
        const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

        document.getElementById("name-error").textContent = "";
        document.getElementById("email-error").textContent = "";
        document.getElementById("password-error").textContent = "";
        document.getElementById("confirm-password-error").textContent = "";

        if (name === "" || name.length < 5 || !namePattern.test(name)) {
            document.getElementById("name-error").textContent = "Enter a valid full name (at least 5 characters).";
            isValid = false;
        }

        if (email === "" || !emailPattern.test(email)) {
            document.getElementById("email-error").textContent = "Enter a valid email address.";
            isValid = false;
        }

        if (password.length < 6) {
            document.getElementById("password-error").textContent = "Password must be at least 6 characters.";
            isValid = false;
        }

        if (confirmPassword !== password) {
            document.getElementById("confirm-password-error").textContent = "Passwords do not match.";
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
        }
    }
    </script>
</body>

</html>