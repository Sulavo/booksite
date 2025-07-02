<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Navbar</title>
    <link rel="stylesheet" href="assets/css/admin-navbar.css">
    <style>
    /* Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        height: 100%;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Admin Navbar */
    .admin-navbar {
        background: linear-gradient(to right, #6a1b9a, #8e24aa);
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        width: 100vw;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        transition: transform 0.4s ease;

    }


    .admin-navbar-hide {
        transform: translateY(-100%);
    }


    .admin-navbar .left-section {
        display: flex;
        align-items: center;
        gap: 25px;
        margin-left: 250px;
        padding-left: 20px;
    }


    .admin-navbar img.logo {
        height: 35px;
    }


    .admin-navbar a {
        color: #fff;
        text-decoration: none;
        font-weight: bold;
        font-size: 1rem;
        padding: 6px 14px;
        border-radius: 6px;
        transition: background 0.3s ease;
        display: flex;
        align-items: center;
        height: 100%;
    }


    .admin-navbar a:hover {
        background-color: rgba(255, 255, 255, 0.15);
    }


    .admin-navbar .right-section {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-right: 250px;
        padding-right: 20px;
    }


    body {
        padding-top: 60px;
    }
    </style>
</head>

<body>


    <div id="adminNavbar" class="admin-navbar">
        <div class="left-section">
            <a href="/booksite/admin/book_list.php">E-Book</a>
            <a href="/booksite/admin/book_list.php">📚 Book List</a>
            <a href="/booksite/admin/users.php">👥 Users</a>
            <a href="/booksite/admin/insert_book.php">➕ Insert Book</a>
        </div>
        <div class="right-section">
            <a href="/booksite/logout.php">📘 Logout</a>
        </div>
    </div>


    <script>
    let lastScrollTop = 0;
    const navbar = document.getElementById('adminNavbar');
    const navbarHeight = navbar.offsetHeight;

    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

        if (scrollTop - lastScrollTop > navbarHeight * 2) {

            navbar.classList.add('admin-navbar-hide');
            lastScrollTop = scrollTop;
        } else if (lastScrollTop - scrollTop > navbarHeight * 2) {

            navbar.classList.remove('admin-navbar-hide');
            lastScrollTop = scrollTop;
        }
    });
    </script>

</body>

</html>