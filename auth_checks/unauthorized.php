<?php include '../includes/navbar.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <style>
    /* Basic reset styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Ensure the body takes full height */
    html,
    body {
        height: 100%;
    }

    /* Ensure content doesn't get hidden behind the navbar */
    body {
        font-family: Arial, sans-serif;
        position: relative;
        padding-top: 60px;
        /* Make space for the fixed navbar */
    }

    /* Center content vertically and horizontally */
    .center-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        text-align: center;
        min-height: 100vh;
        /* Ensure content fills at least the full viewport height */
    }

    /* Access Denied message styling */
    h2 {
        color: #cc0000;
    }

    a {
        margin-top: 20px;
        text-decoration: none;
        color: #007bff;
        font-weight: bold;
    }

    a:hover {
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <!-- Content -->
    <div class="center-wrapper">
        <h2>Access Denied</h2>
        <p>You are not authorized to view this page.</p>
        <a href="../index.php">Go back to homepage</a>
    </div>
</body>

</html>