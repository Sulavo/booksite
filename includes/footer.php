<!-- Footer -->
<footer id="footer" class="site-footer">
    <div class="footer-container">
        <div class="footer-links">
            <a href="/booksite/about.php">About Us</a>
            <a href="/booksite/contact.php">Contact Us</a>
        </div>
        <div class="footer-copyright">
            &copy; <?= date('Y') ?> Online Book Reading. All rights reserved.
        </div>
    </div>
</footer>

<style>
/* Make sure html & body stretch full height */
html,
body {
    height: 100%;
    margin: 0;
    padding: 0;
}

/* Flex layout to push footer down */
body {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Your main page content wrapper */
#content {
    flex: 1;
    padding-bottom: 60px;
    /* Prevents overlap if footer is animated in */
}

/* Footer always at the bottom */
.site-footer {
    background: linear-gradient(to right, #6a1b9a, #8e24aa);
    color: white;
    width: 100%;
    padding: 20px 0;
    text-align: center;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
}

/* Flex container */
.footer-container {
    max-width: 1200px;
    margin: auto;
    padding: 10px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
}

/* Footer links */
.footer-links {
    display: flex;
    gap: 25px;
}

.footer-links a {
    color: white;
    text-decoration: none;
    font-weight: bold;
    font-size: 1rem;
    padding: 6px 14px;
    border-radius: 6px;
    transition: background 0.3s ease;
}

.footer-links a:hover {
    background-color: rgba(255, 255, 255, 0.15);
}

/* Copyright text */
.footer-copyright {
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>