<?php
require_once 'db.php';
require_once 'auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>E-Book</title>
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/searchbar.css">
    <script defer src="assets/js/search_suggest_index.js"></script>

</head>

<body>

    <nav id="navbar" class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <a href="/booksite/index.php">E-Book</a>
            </div>

            <div class="navbar-nav-user-group">
                <ul class="navbar-links">
                    <li><a href="/booksite/index.php">Home</a></li>
                    <li><a href="/booksite/browse.php">Browse</a></li>
                    <li><a href="/booksite/bookmark.php">Bookmarks</a></li>

                    <li>
                        <div class="navbar-search-wrapper">
                            <form id="searchForm" action="/booksite/search.php" method="GET">
                                <div class="search-input-wrapper">
                                    <input type="text" id="searchInput" name="q" class="navbar-search-input"
                                        placeholder="Search by name" autocomplete="off">
                                    <button type="submit" class="search-icon"
                                        style="background: none; border: none; cursor: pointer;">
                                        🔍
                                    </button>
                                </div>
                            </form>
                            <div id="suggestions" class="search-suggestions"></div>
                        </div>
                    </li>
                </ul>

                <ul class="navbar-links">
                    <?php if (isLoggedIn() && isset($_SESSION['user']['username'])): ?>
                    <li class="nav_dropdown">
                        <button class="dropbtn"><?= htmlspecialchars($_SESSION['user']['username']); ?></button>
                        <div class="nav_dropdown-content">
                            <a href="/booksite/update_profile.php">Update Profile</a>
                            <a href="/booksite/logout.php">Logout</a>
                        </div>
                    </li>
                    <?php else: ?>
                    <li><a href="/booksite/login.php">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('navbar');
        let lastScrollTop = 0;
        const threshold = navbar.offsetHeight * 2;

        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            if (scrollTop > lastScrollTop && scrollTop > threshold) {
                navbar.classList.add('navbar-hide');
            } else if (scrollTop < lastScrollTop) {
                navbar.classList.remove('navbar-hide');
            }
            lastScrollTop = Math.max(scrollTop, 0);
        });
    });
    </script>

</body>

</html>